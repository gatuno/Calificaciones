<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Calendario {
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		$calendarios = Gatuf::factory ('Calif_Calendario')->getList ();
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/calendario/index.html',
		                                         array ('page_title' => 'Calendarios',
		                                                'calendarios' => $calendarios),
		                                         $request);
	}
	
	public function cambiarCalendario ($request, $match) {
		if (!empty($request->REQUEST['_redirect_after'])) {
			$success_url = $request->REQUEST['_redirect_after'];
		} else {
			$success_url = Gatuf_HTTP_URL_urlForView ('Calif_Views::index');
		}
		
		if ($request->method == 'POST') {
			/* Si el formulario valida, cambiar el calendario */
			$form = new Calif_Form_Calendario_Seleccionar ($request->POST);
			
			if ($form->isValid ()) {
				$cal = $form->save ();
				
				$request->session->setData ('CAL_ACTIVO', $cal->clave);
			}
		}
		
		return new Gatuf_HTTP_Response_Redirect ($success_url);
	}
}
