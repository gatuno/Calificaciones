<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Materia {
	public function index ($request, $match) {
		/* Listar las materias aquí */
		
		/* TODO: Posiblemente se ocupe la lista de academias */
		$filtro = array();
		$materia = new Calif_Materia ();
		
		/* Aplicando filtrado por carrera y/o departamento */
		if ($request->method == 'POST') {
			$form = new Calif_Form_Materia_Filtrar ($request->POST);
			$filtrado = $form->save ();	
			if($filtrado[0] == 'c'){
				$filtrado = substr($filtrado, 2);
				$request->session->setData('filtro_materia_carrera',$filtrado);
			}
			if($filtrado[0] == 'd'){
				$filtrado = substr($filtrado, 2);
				$request->session->setData('filtro_materia_departamento',$filtrado);
			}
		} else {
			$form = new Calif_Form_Materia_Filtrar(null);
		}

		/* Verificar filtro de materias por carrera */
		$car = $request->session->getData('filtro_materia_carrera', null);
		if (!is_null ($car)){
			$carrera = new Calif_Carrera ();
			$carrera->get ($car);
			$filtro['c'] = 'Carrera de '.$carrera->descripcion;
			$hay = array(strtolower($carrera->_a['model']), 
							 strtolower($materia->_a['model']));
			// Calcular la base de datos que contiene la relación M-N
			if (isset ($GLOBALS['_GATUF_models_related'][$hay[0]][$hay[1]])) {
				// La relación la tiene el $hay[1]
				$dbname = $materia->_con->dbname;
				$dbpfx = $materia->_con->pfx;
			} else {
				$dbname = $carrera->_con->dbname;
				$dbpfx = $carrera->_con->pfx;
			}
			sort($hay);
			$table = $dbpfx.$hay[0].'_'.$hay[1].'_assoc';
			
			$materia->_a['views']['paginador']['join'] = ' LEFT JOIN '.$dbname.'.'.$table.' ON '
					.$carrera->_con->qn(strtolower($materia->_a['model']).'_'.$materia->primary_key).' = '.$carrera->_con->pfx.$carrera->primary_key;
			$key = $carrera->primary_key;
			$materia->_a['views']['paginador']['where'] = $carrera->_con->qn(strtolower($carrera->_a['model']).'_'.$carrera->primary_key).'='.$carrera->_con->esc ($carrera->$key);
		}
		
		$pag = new Gatuf_Paginator ($materia);
		$pag->model_view = 'paginador';
		$pag->action = array ('Calif_Views_Materia::index');
		
		/* Verificr filtro de materias por departamento */
		$dep = $request->session->getData('filtro_materia_departamento',null);
		if(!is_null ($dep) ){
			$departamento = new Calif_Departamento ();
			$departamento->get ($dep);
			$filtro['d'] = $departamento->descripcion;
			$sql = new Gatuf_SQL ('departamento=%s', $dep);
			$pag->forced_where = $sql;
		}
		
		$pag->summary = 'Lista de las materias';
		
		$list_display = array (
			array ('clave', 'Gatuf_Paginator_FKLink', 'Clave'),
			array ('descripcion', 'Gatuf_Paginator_DisplayVal', 'Materia')
		);
		
		if (!is_null ($dep)) {
			$list_display[] = array ('departamento_desc', 'Gatuf_Paginator_DisplayVal', 'Departamento');
		} else {
			$list_display[] = array ('departamento', 'Gatuf_Paginator_FKLink', 'Departamento');
		}
		
		$pag->items_per_page = 40;
		$pag->no_results_text = 'No hay materias';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('clave', 'descripcion', 'departamento_desc'),
			array ('clave', 'descripcion', 'departamento_desc')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/index.html',
		                                         array('page_title' => 'Materias',
		                                         'filtro' => $filtro,
		                                         'form' => $form,
		                                         'paginador' => $pag),
		                                         $request);
	}
	
	public function eliminarFiltro($request, $match){
		if($match[1] == 'd')
			$request->session->setData('filtro_materia_departamento',null);
		if($match[1] == 'c')
			$request->session->setData('filtro_materia_carrera',null);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::index');
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function porDepartamento ($request, $match) {
		$departamento = new Calif_Departamento ();
		if (false === ($departamento->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$request->session->setData('filtro_materia_departamento',$departamento->clave);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function porCarrera ($request, $match) {
		$carrera = new Calif_Carrera ();
		
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$request->session->setData('filtro_materia_carrera',$match[1]);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function verMateria ($request, $match) {
		$materia = new Calif_Materia ();
		
		if (false === ($materia->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::verMateria', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$title = $materia->clave.' '.$materia->descripcion;
		
		/* Listar las secciones de esta materia */
		$seccion = new Calif_Seccion ();
		
		/* Enlaces extras */
		$pag = new Gatuf_Paginator ($seccion);
		$pag->model_view = 'paginador';
		
		$pag->action = array ('Calif_Views_Materia::verMateria', $materia->clave);
		$pag->summary = 'Lista de secciones';
		$list_display = array (
			array ('nrc', 'Gatuf_Paginator_FKLink', 'NRC'),
			array ('materia', 'Gatuf_Paginator_DisplayVal', 'Materia'),
			array ('seccion', 'Gatuf_Paginator_FKLink', 'Sección'),
			array ('maestro', 'Gatuf_Paginator_FKLink', 'Maestro'),
		);
		
		if ($request->user->isJefe() || $request->user->isCoord ()) {
			$list_display[] = array ('asignacion', 'Gatuf_Paginator_FKExtra', 'Asignacion');
		}
		
		$pag->items_per_page = 30;
		$pag->no_results_text = 'No se encontraron secciones';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('nrc', 'materia', 'seccion', 'maestro'),
			array ('nrc', 'materia', 'seccion', 'maestro', 'asignacion')
		);
		
		$sql_filter = new Gatuf_SQL ('materia=%s', $materia->clave);
		$pag->forced_where = $sql_filter;
		$pag->setFromRequest ($request);
		
		/* Recuperar todos las secciones de esta materia */
		Gatuf::loadFunction ('Calif_Utils_displayHora');
		$calendario_materia = new Gatuf_Calendar ();
		$calendario_materia->events = array ();
		$calendario_materia->opts['conflicts'] = false;
		
		$secciones = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql_filter->gen ()));
		
		$salon_model = new Calif_Salon ();
		
		foreach ($secciones as $seccion) {
			$horas = $seccion->get_calif_horario_list (array ('view' => 'paginador'));
			
			foreach ($horas as $hora) {
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
				$cadena_desc = sprintf ('%s <a href="%s">%s</a><br />', $seccion->materia, $url, $seccion->seccion);
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Edificio::verEdificio', $hora->salon_edificio).'#salon_'.$hora->salon;
				$dia_semana = strtotime ('next Monday');
				
				foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
					if ($hora->$dia) {
						$calendario_materia->events[] = array ('start' => date('Y-m-d ', $dia_semana).Calif_Utils_displayHora($hora->inicio),
										             'end' => date('Y-m-d ', $dia_semana).Calif_Utils_displayHora($hora->fin),
										             'title' => $hora->salon_edificio.' '.$hora->salon_aula,
										             'content' => $cadena_desc,
										             'url' => $url, 'color' => is_null ($hora->seccion_asignacion_color) ? '' : '#'.dechex ($hora->seccion_asignacion_color));
					}
					$dia_semana = $dia_semana + 86400;
				}
			}
		}
		
		$carreras = $materia->get_carreras_list ();
		if ($carreras->count () == 0) $carreras = array ();
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/ver-materia.html',
		                                         array('page_title' => $title,
		                                               'materia' => $materia,
		                                               'departamento' => $materia->get_departamento (),
		                                               'calendario' => $calendario_materia,
		                                               'carreras' => $carreras,
		                                               'paginador' => $pag),
		                                         $request);
	}
	
	public function verEval ($request, $match) {
		$materia = new Calif_Materia ();
		
		if (false === ($materia->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::verMateria', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$grupos = Gatuf::factory ('Calif_GrupoEvaluacion')->getList ();
		
		$porcentajes = array ();
		$disponibles = array ();
		$sumas = array ();
		
		foreach ($grupos as $gp) {
			$sql = new Gatuf_SQL ('grupo=%s', $gp->id);
			$porcentajes[$gp->id] = $materia->get_calif_porcentaje_list (array ('filter' => $sql->gen()));
			if ($porcentajes[$gp->id]->count () == 0) $porcentajes[$gp->id] = array ();
			$sumas[$gp->id] = Gatuf::factory ('Calif_Porcentaje')->getGroupSum ($materia->clave, $gp->id);
			$disponibles[$gp->id] = $materia->getNotEvals ($gp->id, true);
		}
		
		$evals = array ();
		
		foreach (Gatuf::factory ('Calif_Evaluacion')->getList () as $eval) {
			$evals[$eval->id] = $eval;
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/ver-eval.html',
		                                         array('page_title' => 'Ver materia',
		                                               'evals' => $evals,
		                                               'grupos' => $grupos,
		                                               'materia' => $materia,
		                                               'disponibles' => $disponibles,
		                                               'porcentajes' => $porcentajes,
		                                               'sumas' => $sumas),
		                                         $request);
	}
		
	public $agregarACarrera_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarACarrera ($request, $match) {
		$title = 'Agregar Materia a una Carrera';
		
		$materia= new Calif_Materia();
		if (($materia->get($match[1]))===false){
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('materia' => $materia);
		if ($request->method == 'POST') {
				$form = new Calif_Form_Materia_AgregarCarrera ($request->POST, $extra);
				
				if ($form->isValid()) {
					$materia = $form->save ();
					
					$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::verMateria', array ($materia->clave));
					return new Gatuf_HTTP_Response_Redirect ($url);
				}
		} else {
			$form = new Calif_Form_Materia_AgregarCarrera (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/agregar-carrera.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form,
		                                                 'materia'=> $materia),
		                                         $request);
	}
		
	public $agregarMateria_precond = array ('Calif_Precondition::jefeRequired');
	public function agregarMateria ($request, $match) {
		$title = 'Nueva materia';
		
		$extra = array ();
		$extra['user'] = $request->user;
		if ($request->method == 'POST') {
			$form = new Calif_Form_Materia_Agregar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$materia = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::verMateria', array ($materia->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Materia_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/agregar-materia.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $actualizarMateria_precond = array ('Calif_Precondition::jefeRequired');
	public function actualizarMateria ($request, $match) {
		$title = 'Actualizar materia';
		
		$materia = new Calif_Materia ();
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->hasPerm ('SIIAU.jefe.'.$materia->departamento)) {
			$request->user->setMessage (3, 'No puede actualizar esta materia, usted no es el Jefe de ese Departamento');
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::verMateria', array ($materia->clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Verificar que la materia esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::actualizarMateria', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('materia' => $materia);
		$extra['user'] = $request->user;
		if ($request->method == 'POST') {
			$form = new Calif_Form_Materia_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$materia = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::verMateria', array ($materia->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Materia_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/edit-materia.html',
		                                         array ('page_title' => $title,
		                                                'materia' => $materia,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $agregarEval_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarEval ($request, $match) {
		$extra = array ();
		
		/* Verificar que el grupo de evaluación exista */
		$grupo_eval = new Calif_GrupoEvaluacion  ();
		
		if (false === ($grupo_eval->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$materia = new Calif_Materia ();
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		/* Verificar que la materia esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::agregarEval', array ($nueva_clave, $match[2]));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$disponibles = $materia->getNotEvals ($grupo_eval->id, true);
		
		if (count ($disponibles) == 0) {
			/* TODO: Lanzar un lindo mensaje de error */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$title = 'Agregar evaluación a la materia "' . $materia->descripcion .'", para '.$grupo_eval->descripcion;
		
		$extra = array ('gp' => $grupo_eval->id, 'materia' => $materia);
		if ($request->method == 'POST') {
			$form = new Calif_Form_Materia_AgregarEval ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::verMateria', array ($materia->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Materia_AgregarEval (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/add-eval.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
}
