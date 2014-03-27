<?php

class Calif_Template_ContextMenu {
	public static function processContext($signal, &$params) {
		$menus = array ();
		
		Gatuf_Signal::send('Calif_Template_ContextMenu::menus', 
                          'Calif_Template_ContextMenu', $menus);
        $params['context'] = array_merge($params['context'], array ('menus' => $menus));
	}
}
