<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Materia {
	
	public function index ($request, $match) {
		/* Listar las materias aquí */
		
		/* TODO: Posiblemente se ocupe la lista de academias */
		$materia = new Calif_Materia ();
		
		$pag = new Gatuf_Paginator ($materia);
		$pag->model_view = 'paginador';
		$pag->action = array ('Calif_Views_Materia::index');
		$pag->summary = 'Lista de las materias';
		
		$list_display = array (
			array ('clave', 'Gatuf_Paginator_FKLink', 'Clave'),
			array ('descripcion', 'Gatuf_Paginator_DisplayVal', 'Materia'),
			array ('departamento', 'Gatuf_Paginator_FKLink', 'Departamento'),
		);
		
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
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public function porDepartamento ($request, $match) {
		$departamento = new Calif_Departamento ();
		
		if (false === ($departamento->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$materia = new Calif_Materia ();
		
		$sql = new Gatuf_SQL ('departamento=%s', $departamento->clave);
		
		$pag = new Gatuf_Paginator ($materia);
		$pag->model_view = 'paginador';
		$pag->forced_where = $sql;
		$pag->action = array ('Calif_Views_Materia::porDepartamento', array ($departamento->clave));
		$pag->summary = 'Lista de las materias';
		
		$list_display = array (
			array ('clave', 'Gatuf_Paginator_FKLink', 'Clave'),
			array ('descripcion', 'Gatuf_Paginator_DisplayVal', 'Materia'),
			array ('departamento_desc', 'Gatuf_Paginator_DisplayVal', 'Departamento'),
		);
		
		$pag->items_per_page = 40;
		$pag->no_results_text = 'No hay materias';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('clave', 'descripcion'),
			array ('clave', 'descripcion')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/por-departamento.html',
		                                         array('page_title' => 'Materias',
		                                               'departamento' => $departamento,
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public function porCarrera ($request, $match) {
		$carrera = new Calif_Carrera ();
		
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$materia = new Calif_Materia ();
		$hay = array(strtolower($carrera->_a['model']), 
						 strtolower($materia->_a['model']));
		sort($hay);
		$table = $hay[0].'_'.$hay[1].'_assoc';
		
		$materia->_a['views']['paginador']['join'] = ' LEFT JOIN '.$carrera->_con->pfx.$table.' ON '
				.$carrera->_con->qn(strtolower($materia->_a['model']).'_'.$materia->primary_key).' = '.$carrera->_con->pfx.$carrera->primary_key;
		$key = $carrera->primary_key;
		$materia->_a['views']['paginador']['where'] = $carrera->_con->qn(strtolower($carrera->_a['model']).'_'.$carrera->primary_key).'='.$carrera->_con->esc ($carrera->$key);
		
		$pag = new Gatuf_Paginator ($materia);
		$pag->model_view = 'paginador';
		$pag->action = array ('Calif_Views_Materia::porCarrera', array ($carrera->clave));
		$pag->summary = 'Lista de las materias';
		
		$list_display = array (
			array ('clave', 'Gatuf_Paginator_FKLink', 'Clave'),
			array ('descripcion', 'Gatuf_Paginator_DisplayVal', 'Materia'),
			array ('departamento', 'Gatuf_Paginator_FKLink', 'Departamento'),
		);
		
		$pag->items_per_page = 40;
		$pag->no_results_text = 'No hay materias';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('clave', 'descripcion'),
			array ('clave', 'descripcion')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/por-carrera.html',
		                                         array('page_title' => 'Materias',
		                                               'carrera' => $carrera,
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public function verMateria ($request, $match) {
		$materia =  new Calif_Materia ();
		
		if (false === ($materia->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::verMateria', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
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
		
		$pag->items_per_page = 30;
		$pag->no_results_text = 'No se encontraron secciones';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('nrc', 'materia', 'seccion', 'maestro'),
			array ('nrc', 'materia', 'seccion', 'maestro')
		);
		
		$sql_filter = new Gatuf_SQL ('materia=%s', $materia->clave);
		$pag->forced_where = $sql_filter;
		$pag->setFromRequest ($request);
		
		/* Recuperar todos las secciones de esta materia */
		Gatuf::loadFunction ('Calif_Utils_displayHoraSiiau');
		$calendario_materia = new Gatuf_Calendar ();
		$calendario_materia->events = array ();
		$calendario_materia->opts['conflicts'] = false;
		
		$secciones = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql_filter->gen ()));
		
		$salon_model = new Calif_Salon ();
		
		foreach ($secciones as $seccion) {
			$sql = new Gatuf_SQL ('nrc=%s', $seccion->nrc);
			$horas = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen ()));
			
			foreach ($horas as $hora) {
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
				$cadena_desc = sprintf ('%s <a href="%s">%s</a><br />', $seccion->materia, $url, $seccion->seccion);
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::verSalon', $hora->salon);
				$dia_semana = strtotime ('next Monday');
				
				foreach (array ('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') as $dia) {
					if ($hora->$dia) {
						$calendario_materia->events[] = array ('start' => date('Y-m-d ', $dia_semana).Calif_Utils_displayHoraSiiau ($hora->hora_inicio),
										             'end' => date('Y-m-d ', $dia_semana).Calif_Utils_displayHoraSiiau ($hora->hora_fin),
										             'title' => $hora->salon_edificio.' '.$hora->salon_aula,
										             'content' => $cadena_desc,
										             'url' => $url, 'color' => is_null ($hora->seccion_asignacion_color) ? '' : '#'.dechex ($hora->seccion_asignacion_color));
					}
					$dia_semana = $dia_semana + 86400;
				}
			}
		}
		
		$carreras = $materia->get_carreras_list ();
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/ver-materia.html',
		                                         array('page_title' => 'Ver materia',
		                                               'materia' => $materia,
		                                               'departamento' => $materia->get_departamento (),
		                                               'calendario' => $calendario_materia,
		                                               'carreras' => $carreras,
		                                               'paginador' => $pag),
		                                         $request);
	}
	
	public function verEval ($request, $match) {
		$materia =  new Calif_Materia ();
		
		if (false === ($materia->getMateria($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::verMateria', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$grupos = Gatuf::factory ('Calif_GrupoEvaluacion')->getList ();
		$sql = new Gatuf_SQL ('materia=%s', $materia->clave);
		$ps = Gatuf::factory ('Calif_Porcentaje')->getList (array ('filter' => $sql->gen ()));
		$porcentajes = array ();
		foreach ($ps as $p) {
			$porcentajes[$p->evaluacion] = $p;
		}
		
		$evals = array ();
		$disponibles = array ();
		$sumas = array ();
		
		foreach ($grupos as $gp) {
			$evals[$gp->id] = $materia->getEvals ($gp->id);
			$sumas[$gp->id] = Gatuf::factory ('Calif_Porcentaje')->getGroupSum ($materia->clave, $gp->id);
			$disponibles[$gp->id] = $materia->getNotEvals ($gp->id, true);
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
	
	public $agregarMateria_precond = array ('Gatuf_Precondition::loginRequired');
	public function agregarMateria ($request, $match) {
		$title = 'Nueva materia';
		
		$extra = array ();
		
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
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/edit-materia.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $actualizarMateria_precond = array ('Gatuf_Precondition::loginRequired');
	public function actualizarMateria ($request, $match) {
		$title = 'Actualizar materia';
		
		$extra = array ();
		
		$materia = new Calif_Materia ();
		if (false === ($materia->getMateria ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		/* Verificar que la materia esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::actualizarMateria', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra['materia'] = $materia;
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
		                                                'form' => $form),
		                                         $request);
	}
	
	public $agregarEval_precond = array ('Gatuf_Precondition::loginRequired');
	public function agregarEval ($request, $match) {
		$extra = array ();
		
		$materia = new Calif_Materia ();
		if (false === ($materia->getMateria ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		/* Verificar que la materia esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::agregarEval', array ($nueva_clave, $match[2]));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Verificar que el grupo de evaluación exista */
		$grupo_eval = new Calif_GrupoEvaluacion  ();
		
		if (false === ($grupo_eval->getGrupoEval ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$disponibles = $materia->getNotEvals ($grupo_eval->id);
		
		if (count ($disponibles) == 0) {
			/* TODO: Lanzar un lindo mensaje de error */
			throw new Exception ('La materia no tiene formas de evaluacion disponibles para este grupo');
		}
		
		$title = 'Agregar evaluación a la materia "' . $materia->descripcion .'", para '.$grupo_eval->descripcion;
		
		$extra = array ('evals' => $disponibles, 'materia' => $materia);
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
