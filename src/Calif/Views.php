<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views {
	function login ($request, $match, $success_url, $extra_context=array()) {
		if (!empty($request->REQUEST['_redirect_after'])) {
			$success_url = $request->REQUEST['_redirect_after'];
		} else {
			$success_url = Gatuf::config ('login_success_url', '/');
		}
		
		$error = '';
		if ($request->method == 'POST') {
			
	}
}
