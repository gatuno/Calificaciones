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
                if($user->type == 'a'){
                	$tipo = 'Alumno ';
                	$usr = new Calif_Alumno ();
                }
                else{
                	$tipo = 'Maestro ';
                 	$usr = new Calif_Maestro ();
                 }
		$usr->get ($usuario) ;
		$permisos_usuario = $user->getAllPermissions();
		if (count($permisos_usuario) == 0 )
			$extra['perm'][]=null;
		foreach($permisos_usuario as $p){
			$extra['perm'][] = strstr($p, 'c');
		}
		
		$form = new Calif_Form_Usuario_Permisos (null, $extra);
		$form2 =new Calif_Form_Usuario_Grupos (null, $extra);
		return Gatuf_Shortcuts_RenderToResponse ('calif/user/permisos.html',
		                                         array('page_title' => 'Permisos',
		                                         	'usuario' => $usr,
		                                         	'tipo' => $tipo,
                                                       'form' => $form,
													   'form2' => $form2
													   ),
                                                 $request);
	}		
	//public $user;
	public function Save($request, $match) {
	
		$extra=array();
		$extra= $match[1];
		$usuario=new Calif_User($extra);
	  	//$form = new Calif_Form_Usuario_Permisos ($request->POST, $extra);
		$permiso=$request->POST['permiso'];
		$permisos=new Gatuf_Permission($permiso);
		$usuario->delAssoc($permisos);
	  
	 $url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::index', array ());
					return new Gatuf_HTTP_Response_Redirect ($url);
	
	}
}
