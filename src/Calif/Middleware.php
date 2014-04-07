<?php

class Calif_Middleware {
	public static function processContext($signal, &$params) {
		$menus = array ();
		
		Gatuf_Signal::send('Calif_Context::menus', 
                          'Calif_Middleware', $menus);
        $params['context'] = array_merge($params['context'], array ('menus' => $menus));
	}
};
