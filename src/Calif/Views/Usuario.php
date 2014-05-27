<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Usuario {
	public $agregarPermiso_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarPermiso($request, $match) {
		$usuario = new Calif_User ();
		
		if (false === ($usuario->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($usuario->type == 'a') {
			/* Por el momento los alumnos no tienen permisos */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('user' => $usuario);
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Usuario_Permisos ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
			}
		}
		
		if ($usuario->type == 'm') {
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::permisos', $usuario->login);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
	}

	public $eliminarPermiso_precond = array ('Gatuf_Precondition::adminRequired');
	public function eliminarPermiso($request, $match) {
		$usuario = new Calif_User ();
		
		if (false === ($usuario->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($usuario->type == 'a') {
			/* Por el momento los alumnos no tienen permisos */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($permiso = new Gatuf_Permission ($match[2])) {
			$usuario->delAssoc($permiso);
		}
		
		if ($usuario->type == 'm') {
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::permisos', $usuario->login);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
	}
	
	public $agregarGrupo_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarGrupo($request, $match) {
		$usuario = new Calif_User ();
		
		if (false === ($usuario->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($usuario->type == 'a') {
			/* Por el momento los alumnos no tienen permisos */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('user' => $usuario);
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Usuario_Grupos ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
			}
		}
		
		if ($usuario->type == 'm') {
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::permisos', $usuario->login);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
	}
	
	public $passwordChange_precond = array ('Gatuf_Precondition::loginRequired');
	public function passwordChange ($request, $match) {
		$extra = array();
		$extra['usuario'] = $request->user;
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Usuario_Password ($request->POST, $extra);
			
			if ($form->isValid()) {
				$usuario = $form->save ();
				
				if($usuario->type == 'a'){
					$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Alumno::verAlumno', array ($usuario->login));
				} else {
					$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::verMaestro', array ($usuario->login));
				}
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Usuario_Password (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/user/cambiar-password.html',
		                                         array ('page_title' => 'Cambiar Contraseña',
		                                                'form' => $form),
		                                         $request);
	}
}
