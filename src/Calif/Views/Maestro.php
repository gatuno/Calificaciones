<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Maestro {
	public function index ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		$dep = $request->session->getData('filtro_profesor_departamento', null);
		$filtro = null;
		$pag = new Gatuf_Paginator ($maestro);
		if (!is_null ($dep)){
			$departamento = new Calif_Departamento ();
		
			if (false === ($departamento->get ($dep))) {
				$dep = null;
				$request->session->setData('filtro_profesor_departamento', null);
			} else {
				$filtro = $departamento->descripcion;
				$sql = new Gatuf_SQL ('departamento=%s', $dep);
				$pag->model_view = 'maestros_departamentos';
				$pag->forced_where = $sql;
			}
		}
		$pag->action = array ('Calif_Views_Maestro::index');
		$pag->summary = 'Lista de maestros';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_FKLink', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
			array ('grado', 'Gatuf_Paginator_FKExtra', 'Grado'),
		);

		if (!is_null ($dep) && $request->user->hasPerm ('SIIAU.jefe.'.$dep)){
			array_push($list_display, array('departamento', 'Gatuf_Paginator_FKLink', 'Horario'));
		}

		$pag->items_per_page = 50;
		$pag->no_results_text = 'No se encontraron profesores';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/index.html',
		                                         array('page_title' => 'Profesores',
		                                               'filtro' => $filtro,
		                                               'paginador' => $pag),
		                                         $request);
	}
	
	public function porDepartamento ($request, $match) {
		$departamento = new Calif_Departamento ();
		
		if (false === ($departamento->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$request->session->setData('filtro_profesor_departamento',$departamento->clave);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Maestro::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}

	public function eliminarFiltro($request, $match){
		$request->session->setData('filtro_profesor_departamento',null);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Maestro::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function verMaestro ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		if (false === ($maestro->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$maestro->getUser ();
		if ($maestro->nombramiento !== null) {
			$nombramiento = new Calif_Nombramiento ($maestro->nombramiento);
		} else {
			$nombramiento = null;
		}
		$asignatura = new Calif_Nombramiento ($maestro->asignatura);
		$title = (($maestro->sexo == 'M') ? 'Profesor ':'Profesora ').$maestro->nombre.' '.$maestro->apellido;
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/ver-maestro.html',
		                                         array('page_title' => $title,
		                                               'maestro' => $maestro,
		                                               'nombramiento' => $nombramiento,
		                                               'asignatura' => $asignatura),
		                                         $request);
	}
	
	public function verHorario ($request, $match) {
		Gatuf::loadFunction ('Calif_Utils_displayHora');
		$maestro = new Calif_Maestro ();
		
		if (false === ($maestro->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$maestro->getUser ();
		
		$depas = array ();
		$sql = new Gatuf_SQL ('codigo=%s', $maestro->codigo);
		
		foreach (Gatuf::factory ('Calif_Maestro')->getList (array ('view' => 'maestros_departamentos', 'filter' => $sql->gen ())) as $d) {
			$depas[] = Gatuf::factory ('Calif_Departamento', $d->departamento);
		}
		$grupos = $maestro->get_primario_list (array ('view' => 'paginador'));
		if( $grupos_suplente = $maestro->get_suplente_list (array ('view' => 'paginador')) ){
			foreach ($grupos_suplente as $suple) {
			$grupos[] = $suple;
			}
		}
		if (count ($grupos) == 0) {
			$horario_maestro = null;
			$grupos = array ();
		} else {
			$horario_maestro = new Gatuf_Calendar ();
			$horario_maestro->events = array ();
			$horario_maestro->opts['conflicts'] = true;
			$horario_maestro->opts['conflict-color'] = '#FFE428';
			
			foreach ($grupos as $grupo) {
				if(!$grupo->suplente || $grupo->suplente == $maestro->codigo){
					$horas = $grupo->get_calif_horario_list (array ('view' => 'paginador'));
					
					foreach ($horas as $hora) {
						$cadena_desc = $grupo->materia.' '.$grupo->seccion;
						$dia_semana = strtotime ('next Monday');
						
						foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
							if ($hora->$dia) {
								$horario_maestro->events[] = array ('start' => date('Y-m-d ', $dia_semana).Calif_Utils_displayHora ($hora->inicio),
												             'end' => date('Y-m-d ', $dia_semana).Calif_Utils_displayHora ($hora->fin),
												             'content' => $hora->salon_edificio.' '.$hora->salon_aula.'<br />'.$cadena_desc,
												             'title' => '',
												             'url' => '.', 'color' => is_null ($hora->seccion_asignacion_color) ? '' : '#'.dechex ($hora->seccion_asignacion_color));
							}
							$dia_semana = $dia_semana + 86400;
						}
					}
				}
			}
		}
		
		$title = (($maestro->sexo == 'M') ? 'Profesor ':'Profesora ').$maestro->nombre.' '.$maestro->apellido;
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/ver-horario.html',
		                                         array('page_title' => $title,
		                                               'maestro' => $maestro,
		                                               'calendario' => $horario_maestro,
		                                               'departamentos' => $depas,
                                                       'grupos' => $grupos),
                                                 $request);
	}
	
	public function verCarga ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		if (false === ($maestro->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$oficio = $request->session->getData ('numero_oficio', 1);
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_EstablecerOficio ($request->POST, array ('numero' => $oficio));
			
			if ($form->isValid ()) {
				$oficio = $form->save ();
				
				$request->session->setData ('numero_oficio', $oficio);
			}
		} else {
			$form = new Calif_Form_EstablecerOficio (null, array ('numero' => $oficio));
		}
		
		$maestro->getUser ();
		if ($maestro->nombramiento !== null) {
			$nombramiento = new Calif_Nombramiento ($maestro->nombramiento);
		} else {
			$nombramiento = null;
		}
		$asignatura = new Calif_Nombramiento ($maestro->asignatura);
		
		$totales = array ('t' => $maestro->getCarga ('t'), 'a' => $maestro->getCarga ('a'), 'h' => $maestro->getCarga ('h'));
		
		$grupos = $maestro->get_primario_list (array ('view' => 'paginador'));
		$grupos_suplente = $maestro->get_suplente_list (array ('view' => 'paginador'));
		
		foreach ($grupos_suplente as $suple) {
			$grupos[] = $suple;
		}
		
		$puestos = array ();
		
		foreach ($grupos as $grupo) {
			/* Sólo jalar los números de puestos relevantes para el profesor */
			if ($grupo->maestro == $maestro->codigo) {
				$nps = $grupo->get_calif_numeropuesto_list (array ('filter' => "(tipo = 't' OR tipo = 'p')"));
			} else {
				$nps = $grupo->get_calif_numeropuesto_list (array ('filter' => "(tipo = 'u' OR tipo = 'q')"));
			}
			
			if ($nps->count () == 0) $puestos[$grupo->nrc] = array ();
			else $puestos[$grupo->nrc] = $nps;
		}
		$title = (($maestro->sexo == 'M') ? 'Profesor ':'Profesora ').$maestro->nombre.' '.$maestro->apellido;
		
		$depas = array ();
		$sql = new Gatuf_SQL ('codigo=%s', $maestro->codigo);
		
		foreach (Gatuf::factory ('Calif_Maestro')->getList (array ('view' => 'maestros_departamentos', 'filter' => $sql->gen ())) as $d) {
			$depas[] = Gatuf::factory ('Calif_Departamento', $d->departamento);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/ver-carga.html',
		                                         array('page_title' => $title,
		                                               'maestro' => $maestro,
		                                               'nombramiento' => $nombramiento,
		                                               'asignatura' => $asignatura,
		                                               'totales' => $totales,
		                                               'grupos' => $grupos,
		                                               'puestos' => $puestos,
		                                               'form' => $form,
		                                               'departamentos' => $depas),
                                                 $request);
	}
	
	public $agregarMaestro_precond = array ('Gatuf_Precondition::adminRequired');
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
	
	public $actualizarMaestro_precond = array ('Gatuf_Precondition::adminRequired');
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
	
	public function verHorarioPDF ($request, $match, $params = array ()) {
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
	
	public function constanciaCargaHoraria ($request, $match) {
		$maestro = new Calif_Maestro ();
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$departamento = new Calif_Departamento ();
		
		if (false === $departamento->get ($match[2])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$oficio = $request->session->getData ('numero_oficio', 1);
		
		$pdf = new Calif_PDF_CargaHoraria ('P', 'mm', 'Letter');
		$pdf->profesor = $maestro;
		$pdf->departamento = $departamento;
		$pdf->oficio = $oficio;
		
		$pdf->renderBase ();
		
		$pdf->Close ();
		
		$oficio++;
		$request->session->setData ('numero_oficio', $oficio);
		
		$nombre_pdf = 'carga_horaria_'.$maestro->codigo.'_'.$departamento->clave.'.pdf';
		$pdf->Output ('/tmp/'.$nombre_pdf, 'F');
		
		return new Gatuf_HTTP_Response_File ('/tmp/'.$nombre_pdf, $nombre_pdf, 'application/pdf', true);
	}
	
	public $permisos_precond = array ('Gatuf_Precondition::adminRequired');
	public function permisos ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$maestro->getUser ();
		$extra = array ('user' => $maestro->user);
		
		$title = (($maestro->sexo == 'M') ? 'Profesor ':'Profesora ').$maestro->nombre.' '.$maestro->apellido;
		
		$permisos_usuario = $maestro->user->getAllPermissions();
		if ($maestro->user->administrator || count ($permisos_usuario) == Gatuf::factory ('Gatuf_Permission')->getCount ()) {
			/* Tiene todos los permisos, no hay nada que agregar */
			$form = null;
		} else {
			$form = new Calif_Form_Usuario_Permisos (null, $extra);
		}
		
		
		$grupos = $maestro->user->get_groups_list ();
		
		if (count ($grupos) == Gatuf::factory ('Gatuf_Group')->getCount ()) {
			$form2 = null;
		} else {
			$form2 = new Calif_Form_Usuario_Grupos (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/permisos.html',
		                                         array( 'page_title' => $title,
		                                                'maestro' => $maestro,
		                                                'permisos' => $permisos_usuario,
		                                                'grupos' => $grupos,
		                                                'form' => $form,
		                                                'form2' => $form2),
		                                         $request);
	}
}
