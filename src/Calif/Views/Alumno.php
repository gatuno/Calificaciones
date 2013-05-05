<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Alumno {
	public function index ($request, $match) {
		/* Utilizar un paginador aquí, por favor */
		
		/* Recuperar todas las carreras para la paginación */
		$carreras = Gatuf::factory('Calif_Carrera')->getList ();
		$extra = array ();
		foreach ($carreras as $car) {
			$extra [$car->clave] = $car->descripcion;
		}
		
		$alumno =  new Calif_Alumno ();
		
		$pag = new Gatuf_Paginator ($alumno);
		$pag->extra = $extra;
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
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido', 'carrera')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/alumno/index.html',
		                                         array('page_title' => 'Alumnos',
                                                       'paginador' => $pag),
                                                 $request);
	}
	
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
}
