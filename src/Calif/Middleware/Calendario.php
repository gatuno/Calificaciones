<?php

class Calif_Middleware_Calendario {
	function process_request (&$request) {
		if (preg_match('#^/$#', $request->query) || preg_match('#^/login/$#', $request->query) || preg_match('#^/logout/$#', $request->query)) {
			return false;
		}
		
		if (preg_match('#^/password#', $request->query)) {
			return false;
		}
		
		$request->calendario = new Calif_Calendario ();
		if (preg_match('#^/calendarios#', $request->query)) {
			return false;
		}
		
		$cal = $request->session->getData ('CAL_ACTIVO', null);
		
		if ($cal == null) {
			/* TODO: Leer desde la base de datos o leer desde el archivo de configuración */
			$cal = '201410';
		}
		
		if (false === $request->calendario->get($cal)) {
			/* Si no existe el calendario seleccionado,
			 * Seleccionar el primero que no esté oculto */
			$ops = $request->calendario->getList (array ('filter' => 'oculto=0', 'order' => 'clave DESC'));
			
			if ($ops->count ()) {
				/* No hay calendarios disponibles, redirigir hacia la vista de calendarios */
				return new Gatuf_HTTP_Response_NotFound ($request);
			}
			
			$request->calendario = $ops[0];
		}
		
		$GLOBALS['CAL_ACTIVO'] = $request->calendario->clave;
		$request->session->setData ('CAL_ACTIVO', $request->calendario->clave);
		
		Gatuf_Signal::connect('Gatuf_Template_Context_Request::construct', array('Calif_Middleware_Calendario', 'processContext'));
		return false;
	}
	
	public static function processContext($signal, &$params) {
        $params['context'] = array_merge($params['context'], array ('form_calendario' => new Calif_Form_Calendario_Seleccionar (null)));
	}
}

