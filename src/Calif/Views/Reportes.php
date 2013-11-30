<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Reportes {
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('calif/reportes/index.html',
		                                  array ('page_title' => 'Reportes'),
		                                  $request);
	}
}
