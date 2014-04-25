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
                	$tipo = 'Profesor ';
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
		$form2 = new Calif_Form_Usuario_Grupos (null, $extra);
		return Gatuf_Shortcuts_RenderToResponse ('calif/user/permisos.html',
		                                         array('page_title' => 'Permisos',
		                                         	'usuario' => $usr,
		                                         	'tipo' => $tipo,
                                                       'form' => $form,
													   'form2' => $form2
													   ),
                                                 $request);
	}		
	public function AlterarPermiso($request, $match) {
	
		$extra=array();
		$extra= $match[1];
		$usuario=new Calif_User($extra);
		$permiso=$request->POST['permiso'];
		$permisos=new Gatuf_Permission($permiso);
		$usuario->SetAssoc($permisos);
	  
	 $url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::index', array ());
					return new Gatuf_HTTP_Response_Redirect ($url);
	
	}
	
	public function AlterarGrupo($request, $match) {
	
		$extra=array();
		$extra= $match[1];
		$usuario=new Calif_User($extra);
		$grupo=$request->POST['grupo'];
		$grupos=new Gatuf_Group($grupo);
		$usuario->SetAssoc($grupos);
	  
	 $url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::index', array ());
					return new Gatuf_HTTP_Response_Redirect ($url);
	
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
