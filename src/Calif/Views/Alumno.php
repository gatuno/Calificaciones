<?php

class Calif_Views_Alumno {
	public function index ($request, $match) {
		/* Utilizar un paginador aquí, por favor */
		/* $garticle = new YourApp_Article();
$pag = new Pluf_Paginator($garticle);
$pag->action = array('YourApp_Views::listArticles');
$pag->summary = __('This table shows a list of the articles.');
$list_display = array(
       array('id', 'Pluf_Paginator_ToString', __('title')),
       array('modif_dtime', 'Pluf_Paginator_DateYMD', __('modified')),
       array('status', 'Pluf_Paginator_DisplayVal', __('status')), 
                     );
$pag->items_per_page = 50;
$pag->no_results_text = __('No articles were found.');
$pag->configure($list_display, 
                array('content'), 
                array('status', 'modif_dtime')
               );
$pag->setFromRequest($request);*/
		$alumno =  new Calif_Alumno ();
		
		$pag = new Gatuf_Paginator ($alumno);
		$pag->action = array ('Calif_Views_Alumno::index');
		$pag->summary = 'Lista de los alumnos';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_DisplayVal', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
			array ('carrera', 'Gatuf_Paginator_DisplayVal', 'Carrera'), /* FIXME: Foreign Key */
		);
		
		$pag->items_per_page = 50;
		$pag->no_results_text = 'No se encontraron alumnos';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido', 'carrera')
		);
		
		$pag->setFromRequest ($request);
		
		$context = new Gatuf_Template_Context(array('page_title' => 'Alumnos',
                                                   'paginador' => $pag)
                                            );
		$tmpl = new Gatuf_Template('calif/alumno/index.html');
		return new Gatuf_HTTP_Response($tmpl->render($context));
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
		
		$tmpl = new Gatuf_Template ('calif/alumno/edit-alumno.html');
		$context = new Gatuf_Template_Context (array ('page_title' => $title, 'form' => $form));
		return new Gatuf_HTTP_Response ($tmpl->render ($context));
	}
}
