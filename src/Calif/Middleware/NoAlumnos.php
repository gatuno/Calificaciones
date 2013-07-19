<?php

/* Middleware para bloquear Alumnos
 *
 * Cuando este middleware se activa, bloquea a todos los alumnos.
 * Cierra todas las vistas excepto la vista de login y la raiz.
 * Si se intenta acceder a otra página que no requiere login,
 * Se solicita login.
 * En caso de que la sesión sea de alumno, se mostrará un
 * error 403.
 */
class Calif_Middleware_NoAlumnos {
	
	function process_request(&$request) {
		if (preg_match('#^/$#', $request->query) || preg_match('#^/login/$#', $request->query) || preg_match('#^/logout/$#', $request->query)) {
			return false;
		}
		
		if (!isset($request->user) or $request->user->isAnonymous()) {
			return new Gatuf_HTTP_Response_RedirectToLogin($request);
		}
		if (!$request->user->active) {
			return new Gatuf_HTTP_Response_Forbidden($request);
		}
		
		if (is_a ($request->user, 'Calif_Alumno')) {
			return new Gatuf_HTTP_Response_Forbidden($request);
		}
		return false;
	}
}
