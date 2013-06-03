<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Horario {
	public $agregarHora_precond = array ('Gatuf_Precondition::loginRequired');
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
	
	public $eliminarHora_precond = array ('Gatuf_Precondition::loginRequired');
	public function eliminarHora ($request, $match) {
		$title = 'Eliminar hora de una sección';
		
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->getNrc($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$hora = new Calif_Horario ();
		
		if (false === ($hora->getHorario ($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($hora->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$salon = new Calif_Salon ();
		$salon->getSalonById ($hora->salon);
		
		if ($request->method == 'POST') {
			/* Adelante, eliminar esta hora */
			$hora->delete ();
			
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($seccion->nrc));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/horario/eliminar-horario.html',
		                                         array ('page_title' => $title,
		                                                'seccion' => $seccion,
		                                                'salon' => $salon,
		                                                'horario' => $hora),
		                                         $request);
	}
	
	public $actualizarHora_precond = array ('Gatuf_Precondition::loginRequired');
	public function actualizarHora ($request, $match) {
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->getNrc($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$hora = new Calif_Horario ();
		
		if (false === ($hora->getHorario ($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($hora->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$title = 'Actualizar horario';
		
		$extra = array('seccion' => $seccion, 'horario' => $hora);
		if ($request->method == 'POST') {
			$form = new Calif_Form_Horario_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$horario = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($horario->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Horario_Actualizar (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/horario/edit-horario.html',
		                                         array ('page_title' => $title,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
}
