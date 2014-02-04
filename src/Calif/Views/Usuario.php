<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Usuario {
	
	public function index($request, $match) {
	}
	
	public function permisos ( $request, $match ){
		$usuario = $match[1];
		$extra = array();
		$sql = new Gatuf_SQL ('login=%s', $usuario);
		$user = Gatuf::factory ('Calif_User')->getOne (array ('filter' => $sql->gen ()));
		if(!$user){
			return Gatuf_Shortcuts_RenderToResponse ('calif/user/error.html',
		                                         array('page_title' => 'Permisos'),
  		                                        $request);
                }
		$permisos_usuario = $user->getAllPermissions();
		if (count($permisos_usuario) == 0 )
			$extra['perm'][]=null;
		foreach($permisos_usuario as $p){
			$extra['perm'][] = strstr($p, 'c');
		}
		if ($request->method == 'POST') {
			$form = new Calif_Form_Usuario_Permisos ($request->POST, $extra);
		}
		else {
			$form = new Calif_Form_Usuario_Permisos (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/user/permisos.html',
		                                         array('page_title' => 'Permisos',
		                                         	'usuario' => $usuario,
                                                       'form' => $form),
                                                 $request);
	}		
}
