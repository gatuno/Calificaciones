<?php

Pluf::loadFunction('Pluf_HTTP_URL_urlForView');
Pluf::loadFunction('Pluf_Shortcuts_RenderToResponse');
Pluf::loadFunction('Pluf_Shortcuts_GetObjectOr404');
Pluf::loadFunction('Pluf_Shortcuts_GetFormForModel');

class Calif_Views_Carrera {
	
	public function index ($request, $match) {
		# Listar las carreras aquí
		$carreras = Pluf::factory('Calif_Carrera')->getList();
		
		$context = new Pluf_Template_Context(array('page_title' => 'Carreras',
                                                   'carreras' => $carreras)
                                            );
		$tmpl = new Pluf_Template('calif/carrera/index.html');
		return new Pluf_HTTP_Response($tmpl->render($context));
	}
	
	public function verCarrera ($request, $match) {
		return new Pluf_HTTP_Response('Hola');
	}
}
