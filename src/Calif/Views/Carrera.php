<?php

/*Pluf::loadFunction('Pluf_HTTP_URL_urlForView');
Pluf::loadFunction('Pluf_Shortcuts_GetObjectOr404');
Pluf::loadFunction('Pluf_Shortcuts_GetFormForModel');*/

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Carrera {
	
	public function index ($request, $match) {
		# Listar las carreras aquí
		$carreras = Gatuf::factory('Calif_Carrera')->getList();
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/carrera/index.html',
		                                         array('page_title' => 'Carreras',
		                                               'carreras' => $carreras),
		                                         $request);
	}
	
	public function verCarrera ($request, $match) {
		/* Ver si esta carrera es válida */
		$carrera = new Calif_Carrera ();
		if (false === ($carrera->getCarrera ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		/* Verificar que la carrera esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Carrera::verCarrera', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$page_title = 'Carrera "'.$carrera->descripcion.'"';
		
		/* No se necesitan recuperar todas las carreras, solo la que estamos viendo */
		$extra = array ($carrera->clave => $carrera->descripcion);
		
		$alumno =  new Calif_Alumno ();
		
		$pag = new Gatuf_Paginator ($alumno);
		$pag->extra = $extra;
		/* Forzar a solo ver los de esta carrera */
		$sql = new Gatuf_SQL ('Carrera=%s', array ($carrera->clave));
		$pag->forced_where = $sql;
		
		$pag->action = array ('Calif_Views_Carrera::verCarrera', $match[1]);
		$pag->summary = 'Lista de los alumnos';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_DisplayVal', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
			array ('carrera', 'Gatuf_Paginator_FKExtra', 'Carrera'),
		);
		
		$pag->items_per_page = 50;
		$pag->no_results_text = 'No se encontraron alumnos';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/carrera/carrera.html',
		                                         array('page_title' => $page_title,
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public $agregarCarrera_precond = array ('Gatuf_Precondition::loginRequired');
	public function agregarCarrera ($request, $match) {
		$title = 'Crear carrera';
		$extra = array ();
		if ($request->method == 'POST') {
			$form = new Calif_Form_Carrera_Agregar($request->POST, $extra);
			if ($form->isValid()) {
				$carrera = $form->save();
				$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Carrera::index');
				return new Gatuf_HTTP_Response_Redirect($url);
			}
		} else {
			$form = new Calif_Form_Carrera_Agregar(null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/carrera/edit-carrera.html',
		                                         array('page_title' => $title,
		                                               'form' => $form),
		                                         $request);
	}
	
	public $actualizarCarrera_precond = array ('Gatuf_Precondition::loginRequired');
	public function actualizarCarrera ($request, $match) {
		$title = 'Actualizar carrera';
		$extra = array ();
		
		$carrera = new Calif_Carrera ();
		if (false === ($carrera->getCarrera ($match[1]))) {
			throw new Pluf_HTTP_Error404();
		}
		/* Verificar que la carrera esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Carrera::actualizarCarrera', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra['carrera'] = $carrera;
		if ($request->method == 'POST') {
			$form = new Calif_Form_Carrera_Actualizar($request->POST, $extra);
			
			if ($form->isValid()) {
				$carrera = $form->save ();
				$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Carrera::index');
				return new Gatuf_HTTP_Response_Redirect($url);
			}
		} else {
			$form = new Calif_Form_Carrera_Actualizar(null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/carrera/edit-carrera.html',
		                                         array('page_title' => $title,
		                                               'form' => $form),
		                                         $request);
	}
}
