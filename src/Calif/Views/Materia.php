<?php

/*Pluf::loadFunction('Pluf_HTTP_URL_urlForView');
Pluf::loadFunction('Pluf_Shortcuts_GetObjectOr404');
Pluf::loadFunction('Pluf_Shortcuts_GetFormForModel');*/

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Materia {
	
	public function index ($request, $match) {
		/* Listar las materias aquí */
		
		/* TODO: Posiblemente se ocupe la lista de academias */
		$materia = new Calif_Materia ();
		
		$pag = new Gatuf_Paginator ($materia);
		$pag->action = array ('Calif_Views_Materia::index');
		$pag->summary = 'Lista de las materias';
		
		$list_display = array (
			array ('clave', 'Gatuf_Paginator_FKLink', 'Clave'),
			array ('descripcion', 'Gatuf_Paginator_DisplayVal', 'Materia'),
		);
		
		$pag->items_per_page = 40;
		$pag->no_results_text = 'No hay materias';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('clave', 'descripcion'),
			array ('clave', 'descripcion')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/index.html',
		                                         array('page_title' => 'Materias',
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public function verMateria ($request, $match) {
		$materia =  new Calif_Materia ();
		
		if (false === ($materia->getMateria($match[1]))) {
			throw new Pluf_HTTP_Error404();
		}
		
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::verMateria', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$e = new Calif_Evaluacion ();
		$grupos = $e->getGruposEvals ();
		$evals = array ();
		$disponibles = array ();
		
		foreach ($grupos as $id_grupo => $grupo) {
			$sql = new Gatuf_SQL ('Grupo=%s', $id_grupo);
			$evals[$id_grupo] = $materia->getEvals ($sql);
			$disponibles[$id_grupo] = $materia->getNotEvals ($sql, true);
		}
		
		/* Listar las secciones de esta materia */
		$seccion = new Calif_Seccion ();
		
		/* Enlaces extras */
		$pag = new Gatuf_Paginator ($seccion);
		
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
		
		$sql_filter = new Gatuf_SQL ('Materia=%s', $materia->clave);
		$pag->forced_where = $sql_filter;
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/materia/ver-materia.html',
		                                         array('page_title' => 'Ver materia',
		                                               'evals' => $evals,
		                                               'grupos' => $grupos,
		                                               'materia' => $materia,
		                                               'disponibles' => $disponibles,
		                                               'paginador' => $pag),
		                                         $request);
	}
	
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
		$descr_grupo = Gatuf::factory ('Calif_Evaluacion')->getGrupoEval ($match[2]);
		if ($descr_grupo === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$filtro = new Gatuf_SQL ('Grupo=%s', $match[2]);
		$disponibles = $materia->getNotEvals ($filtro);
		
		if (count ($disponibles) == 0) {
			/* TODO: Lanzar un lindo mensaje de error */
			throw new Exception ('La materia no tiene formas de evaluacion disponibles para este grupo');
		}
		
		$title = 'Agregar evaluación a la materia "' . $materia->descripcion .'", para '.$descr_grupo;
		
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
