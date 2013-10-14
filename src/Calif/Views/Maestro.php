<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Maestro {
	public function index ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		$pag = new Gatuf_Paginator ($maestro);
		$pag->action = array ('Calif_Views_Maestro::index');
		$pag->summary = 'Lista de maestros';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_FKLink', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
		);
		
		$pag->items_per_page = 50;
		$pag->no_results_text = 'No se encontraron maestros';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/index.html',
		                                         array('page_title' => 'Maestros',
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public function porDepartamento ($request, $match) {
		$departamento = new Calif_Departamento ();
		
		if (false === ($departamento->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$maestro_model = new Calif_Maestro ();
		
		$sql = new Gatuf_SQL ('departamento=%s', $departamento->clave);
		
		$pag = new Gatuf_Paginator ($maestro_model);
		$pag->model_view = 'maestros_departamentos';
		$pag->forced_where = $sql;
		
		$pag->action = array ('Calif_Views_Maestro::porDepartamento', $departamento->clave);
		$pag->summary = 'Lista de maestros';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_FKLink', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
			array ('departamento', 'Gatuf_Paginator_FKLink', 'Horario'),
		);
		
		$pag->items_per_page = 50;
		$pag->no_results_text = 'No hay maestros en este departamento';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido')
		);
		
		$pag->setFromRequest ($request);
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/por-departamento.html',
		                                         array('page_title' => 'Maestros',
                                                       'paginador' => $pag,
                                                       'departamento' => $departamento),
                                                 $request);
	}
	
	public function verMaestro ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		if (false === ($maestro->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$maestro->getUser ();
		$grupos = $maestro->get_calif_seccion_list (array ('view' => 'paginador'));
		
		if (count ($grupos) == 0) {
			$horario_maestro = null;
		} else {
			$horario_maestro = new Gatuf_Calendar ();
			$horario_maestro->events = array ();
			$horario_maestro->opts['conflicts'] = true;
			$horario_maestro->opts['conflict-color'] = '#FFE428';
			
			$salon_model = new Calif_Salon ();
			foreach ($grupos as $grupo) {
				$horas = $grupo->get_calif_horario_list ();
				
				foreach ($horas as $hora) {
					$cadena_desc = $grupo->materia . ' ' . $grupo->seccion.'<br />';
					$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::verSalon', $hora->salon);
					$dia_semana = strtotime ('next Monday');
					
					$salon_model->get ($hora->salon);
					foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
						if ($hora->$dia) {
							$horario_maestro->events[] = array ('start' => date('Y-m-d ', $dia_semana).$hora->inicio,
											             'end' => date('Y-m-d ', $dia_semana).$hora->fin,
											             'title' => $salon_model->edificio.' '.$salon_model->aula,
											             'content' => $cadena_desc,
											             'url' => $url, 'color' => '');
						}
						$dia_semana = $dia_semana + 86400;
					}
				}
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/ver-maestro.html',
		                                         array('page_title' => 'Perfil público',
		                                               'maestro' => $maestro,
		                                               'calendario' => $horario_maestro,
                                                       'grupos' => $grupos),
                                                 $request);
	}
	
	public $agregarMaestro_precond = array ('Gatuf_Precondition::loginRequired');
	public function agregarMaestro ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Calif_Form_Maestro_Agregar ($request->POST);
			
			if ($form->isValid()) {
				$maestro = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::verMaestro', array ($maestro->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Maestro_Agregar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/agregar-maestro.html',
		                                         array ('page_title' => 'Nuevo profesor',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $actualizarMaestro_precond = array ('Gatuf_Precondition::loginRequired');
	public function actualizarMaestro ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$maestro->getUser ();
		$extra = array ('maestro' => $maestro);
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Maestro_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$maestro = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::verMaestro', array ($maestro->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Maestro_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/edit-maestro.html',
		                                         array ('page_title' => 'Actualizar profesor',
		                                                'maestro' => $maestro,
		                                                'form' => $form),
		                                         $request);
	}
	
	public function verHorario ($request, $match, $params = array ()) {
		$maestro = new Calif_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if (!isset ($params['general'])) {
			$departamento = new Calif_Departamento ();
			
			if (false === $departamento->get ($match[2])) {
				throw new Gatuf_HTTP_Error404();
			}
			
			$sql = new Gatuf_SQL ('materia_departamento=%s', $departamento->clave);
			$where = $sql->gen ();
		} else {
			$departamento = null;
			$where = null;
		}
		
		$secciones = $maestro->get_calif_seccion_list (array ('filter' => $where, 'view' => 'paginador'));
		
		$horarios = array ();
		$total_horarios = 0;
		foreach ($secciones as $seccion) {
			$horarios[$seccion->nrc] = $seccion->get_calif_horario_list (array ('view' => 'paginador'));
			$total_horarios += count ($horarios[$seccion->nrc]);
		}
		
		$pdf = new Calif_PDF_Horario ('P', 'mm', 'Letter');
		$pdf->maestro = $maestro;
		$pdf->renderBase ();
		
		$pdf->secciones = $secciones;
		$pdf->horarios = $horarios;
		$pdf->total_horarios = $total_horarios;
		$pdf->departamento = $departamento;
		$pdf->renderHorario ();
		if (!is_null ($departamento)) {
			$pdf->renderFirmas ();
		}
		$pdf->Close ();
		
		$nombre_pdf = $maestro->codigo;
		if (!is_null ($departamento)) {
			$nombre_pdf .= '_departamento_'.$departamento->clave;
		}
 		
		$nombre_pdf .= '.pdf';
		$pdf->Output ('/tmp/'.$nombre_pdf, 'F');
		
		return new Gatuf_HTTP_Response_File ('/tmp/'.$nombre_pdf, $nombre_pdf, 'application/pdf', true);
	}
}
