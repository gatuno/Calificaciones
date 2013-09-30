<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Alumno {
	public $index_precond = array ('Gatuf_Precondition::loginRequired');
	public function index ($request, $match) {
		$alumno =  new Calif_Alumno ();
		
		$pag = new Gatuf_Paginator ($alumno);
		$pag->action = array ('Calif_Views_Alumno::index');
		$pag->summary = 'Lista de los alumnos';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_DisplayVal', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
			array ('carrera', 'Gatuf_Paginator_FKLink', 'Carrera'),
		);
		
		$pag->items_per_page = 50;
		$pag->no_results_text = 'No se encontraron alumnos';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido', 'carrera'),
			array ('codigo', 'nombre', 'apellido', 'carrera')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/alumno/index.html',
		                                         array('page_title' => 'Alumnos',
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public $agregarAlumno_precond = array ('Gatuf_Precondition::loginRequired');
	public function agregarAlumno ($request, $match) {
		$title = 'Nuevo alumno';
		
		$extra = array ();
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Alumno_Agregar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Alumno::verAlumno', array ($alumno->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Alumno_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/alumno/edit-alumno.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
	
	public function evaluar ($request, $match){
		/* Se recibe:
		 * alumno en match[1]
		 * nrc en match[2]
		 * grupo_evaluacion en match[3]
		 */
		$grupo =  new Calif_GrupoEvaluacion ();
		if (false === ($grupo->getGrupoEval ($match[3]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$alumno = new Calif_Alumno ();
		if (false === ($alumno->getAlumno ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$seccion = new Calif_Seccion ();
		if (false === ($seccion->getNrc ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Verificar que el alumno pertenezca a la sección */
		$sql = new Gatuf_SQL ($seccion->_con->pfx.$seccion->views['__grupos__']['tabla'].'.alumno=%s', $alumno->codigo);
		$count = $seccion->getAlumnosList (array ('count' => true, 'filter' => $sql->gen ()));
		
		if ($count != 1) { /* El dichoso alumno no está en esa sección */
			throw new Gatuf_HTTP_Error404 ();
		}
		$sql = new Gatuf_SQL ('grupo=%s AND materia=%s', array ($grupo->id, $seccion->materia));
		$count = Gatuf::factory ('Calif_Porcentaje')->getList (array ('count' => true, 'filter' => $sql->gen ()));
		
		if ($count == 0) {
			/* Pidieron evaluar sobre algun grupo de evaluacion, pero la materia no tiene formas de evaluacion */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$title = 'Evaluando a '.$alumno->nombre.' '.$alumno->apellido.' ('.$grupo->descripcion.')';
		
		$extra = array ('grupo_eval' => $grupo, 'nrc' => $seccion, 'alumno' => $alumno);
		if ($request->method == 'POST') {
			$form = new Calif_Form_Alumno_Evaluar ($request->POST, $extra);
			if ($form->isValid ()) {
				$form->save ();
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Alumno_Evaluar (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/alumno/evaluar.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
}
