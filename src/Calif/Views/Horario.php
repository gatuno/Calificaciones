<?php

class Calif_Views_Horario {
	public function agregarHora ($request, $match) {
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->getNrc ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$title = 'Agregar un nuevo horario';
		
		$extra = array('seccion' => $seccion);
		if ($request->method == 'POST') {
			$form = new Calif_Form_Horario_Agregar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$horario = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($horario->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Horario_Agregar (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/horario/edit-horario.html',
		                                         array ('page_title' => $title,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
		
	}
	
	public function eliminarHora ($request, $match) {
		
	}
	
	public function actualizarHora ($request, $match) {
		
	}
}
