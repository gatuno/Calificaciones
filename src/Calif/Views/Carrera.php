<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Carrera {
	
	public function index ($request, $match) {
		# Listar las carreras aquí, por división
		$divisiones = Gatuf::factory ('Calif_Division')->getList ();
		
		$carreras = array ();
		foreach ($divisiones as $div) {
			$sql = new Gatuf_SQL ('division=%s', $div->id);
			$carreras [$div->id] = Gatuf::factory('Calif_Carrera')->getList(array ('filter' => $sql->gen ()));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/carrera/index.html',
		                                         array('page_title' => 'Carreras',
		                                               'divisiones' => $divisiones,
		                                               'carreras' => $carreras),
		                                         $request);
	}
	
	public function verCarrera ($request, $match) {
		/* Ver si esta carrera es válida */
		$carrera = new Calif_Carrera ();
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		/* Verificar que la carrera esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Alumno::porCarrera', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$color = str_pad (dechex ($carrera->color), 6, '0', STR_PAD_LEFT);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/carrera/ver-carrera.html',
		                                         array ('carrera' => $carrera,
		                                                'color' => $color,
		                                                'page_title' => $carrera->descripcion),
		                                         $request);
	}
	
	public $agregarCarrera_precond = array ('Gatuf_Precondition::adminRequired');
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
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/carrera/agregar-carrera.html',
		                                         array('page_title' => $title,
		                                               'form' => $form),
		                                         $request);
	}
	
	public $actualizarCarrera_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarCarrera ($request, $match) {
		$title = 'Actualizar carrera';
		$extra = array ();
		
		$carrera = new Calif_Carrera ();
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
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
		                                               'carrera' => $carrera,
		                                               'form' => $form),
		                                         $request);
	}
}
