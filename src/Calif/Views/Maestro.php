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
	
	public function verMaestro ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		if (false === $maestro->getMaestro ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$maestro->getSession();
		$sql = new Gatuf_SQL ('maestro=%s', $maestro->codigo);
		
		$grupos = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen ()));
		
		if (count ($grupos) == 0) {
			$horario_maestro = null;
		} else {
			Gatuf::loadFunction ('Calif_Utils_displayHoraSiiau');
			$horario_maestro = new Gatuf_Calendar ();
			$horario_maestro->events = array ();
			$horario_maestro->opts['conflicts'] = true;
			$horario_maestro->opts['conflict-color'] = '#FFE428';
			
			$salon_model = new Calif_Salon ();
			foreach ($grupos as $grupo) {
				$sql = new Gatuf_SQL ('nrc=%s', $grupo->nrc);
				$horas = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen ()));
				
				foreach ($horas as $hora) {
					$cadena_desc = $grupo->materia . ' ' . $grupo->seccion.'<br />';
					$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::verSalon', $hora->salon);
					$dia_semana = strtotime ('next Monday');
					
					$salon_model->getSalonById ($hora->salon);
					foreach (array ('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') as $dia) {
						if ($hora->$dia) {
							$horario_maestro->events[] = array ('start' => date('Y-m-d ', $dia_semana).Calif_Utils_displayHoraSiiau ($hora->hora_inicio),
											             'end' => date('Y-m-d ', $dia_semana).Calif_Utils_displayHoraSiiau ($hora->hora_fin + 45),
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
		$title = 'Nuevo profesor';
		
		$extra = array ();
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Maestro_Agregar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$maestro = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::verMaestro', array ($maestro->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Maestro_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/edit-maestro.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $actualizarMaestro_precond = array ('Gatuf_Precondition::loginRequired');
	public function actualizarMaestro ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		if (false === $maestro->getMaestro ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
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
		                                         array ('page_title' => 'Actualizar maestro',
		                                                'form' => $form),
		                                         $request);
	}
	
	public function verHorario ($request, $match, $params = array ()) {
		$maestro = new Calif_Maestro ();
		
		if (false === $maestro->getMaestro ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$sql = new Gatuf_SQL ('maestro=%s', $maestro->codigo);
		
		if (!isset ($params['general'])) {
			$departamento = new Calif_Departamento ();
			
			if (false === $departamento->getDepartamento ($match[2])) {
				throw new Gatuf_HTTP_Error404();
			}
			
			$sql->Q('materia_departamento=%s', $departamento->clave);
		}
		
		$secciones = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen ()));
		
		$horarios = array ();
		foreach ($secciones as $seccion) {
			$sql = new Gatuf_SQL ('nrc=%s', $seccion->nrc);
			$horarios[$seccion->nrc] = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen()));
		}
		
		$pdf = new Calif_PDF_Horario ('P', 'mm', 'Letter');
		$pdf->maestro = $maestro;
		$pdf->renderBase ();
		
		$pdf->secciones = $secciones;
		$pdf->horarios = $horarios;
		$pdf->renderHorario ();
		
		$pdf->Close ();
		$pdf->Output ('/tmp/mipdf.pdf', 'F');
		
		return new Gatuf_HTTP_Response_File ('/tmp/mipdf.pdf', 'horario.pdf', 'application/pdf');
	}
}
