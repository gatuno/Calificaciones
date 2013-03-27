<?php

/*Pluf::loadFunction('Pluf_HTTP_URL_urlForView');
Pluf::loadFunction('Pluf_Shortcuts_RenderToResponse');
Pluf::loadFunction('Pluf_Shortcuts_GetObjectOr404');
Pluf::loadFunction('Pluf_Shortcuts_GetFormForModel');*/

class Calif_Views_Carrera {
	
	public function index ($request, $match) {
		# Listar las carreras aquí
		$carreras = Gatuf::factory('Calif_Carrera')->getList();
		
		$context = new Gatuf_Template_Context(array('page_title' => 'Carreras',
                                                   'carreras' => $carreras)
                                            );
		$tmpl = new Gatuf_Template('calif/carrera/index.html');
		return new Gatuf_HTTP_Response($tmpl->render($context));
	}
	
	public function verCarrera ($request, $match) {
		return new Gatuf_HTTP_Response('Hola');
	}
	
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
		//$base = Pluf::f('url_base').Pluf::f('idf_base').'/p/';
		$tmpl = new Gatuf_Template ('calif/carrera/add-carrera.html');
		$context = new Gatuf_Template_Context(array('page_title' => $title, 'form' => $form));
		return new Gatuf_HTTP_Response ($tmpl->render ($context));
	}
	
	/*public function actualizarCarrera ($request, $match) {
		$title = 'Actualizar carrera';
		$extra = array ();
		
		$carrera = Pluf_Shortcuts_GetObjectOr404('Carrera', $match[1]);
		$extra['carrera'] = $carrera;
		if ($request->method == 'POST') {
			$form = new Calif_Form_Carrera_Actualizar($request->POST, $extra);*/
}
