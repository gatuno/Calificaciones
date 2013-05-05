<?php

/*Pluf::loadFunction('Pluf_HTTP_URL_urlForView');
Pluf::loadFunction('Pluf_Shortcuts_GetObjectOr404');
Pluf::loadFunction('Pluf_Shortcuts_GetFormForModel');*/
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Seccion {
	
	public function index ($request, $match) {
		/* Utilizar un paginador aquí, por favor */
		$seccion = new Calif_Seccion ();
		
		/* Enlaces extras */
		$pag = new Gatuf_Paginator ($seccion);
		
		$pag->action = array ('Calif_Views_Seccion::index');
		$pag->summary = 'Lista de secciones';
		$list_display = array (
			array ('nrc', 'Gatuf_Paginator_DisplayVal', 'NRC'),
			array ('materia', 'Gatuf_Paginator_FKLink', 'Materia'),
			array ('seccion', 'Gatuf_Paginator_DisplayVal', 'Sección'),
			array ('maestro', 'Gatuf_Paginator_DisplayVal', 'Maestro'),
		);
		
		$pag->items_per_page = 30;
		$pag->no_results_text = 'No se encontraron secciones';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('nrc', 'materia', 'seccion', 'maestro'),
			array ('nrc', 'materia', 'seccion', 'maestro')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/index.html',
		                                          array ('paginador' => $pag,
		                                                 'page_title' => 'Secciones'),
		                                          $request);
	}
	
	public function verNrc ($request, $match) {
		$title = 'Ver NRC';
		
		$seccion =  new Calif_Seccion ();
		
		if (false === ($seccion->getNrc($match[1]))) {
			throw new Pluf_HTTP_Error404();
		}
		
		$materia = new Calif_Materia ();
		$materia->getMateria ($seccion->materia);
		
		$maestro = new Calif_Maestro ();
		$maestro->getMaestro ($seccion->maestro);
		
		/* TODO: Listar aquí los alumnos de este grupo */
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/ver-seccion.html',
		                                          array ('seccion' => $seccion,
		                                                 'page_title' => $title,
		                                                 'materia' => $materia,
		                                                 'maestro' => $maestro),
		                                          $request);
	}
	
	public function agregarNrc ($request, $match) {
		$title = 'Crear NRC';
		
		$extra = array ();
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Seccion_Agregar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$seccion = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Seccion_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/edit-seccion.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
}
