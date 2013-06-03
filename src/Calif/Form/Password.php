<?php

class Calif_Form_Password extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['account'] = new Gatuf_Form_Field_Varchar(
		                               array('required' => true,
		                                     'label' => 'Tu código o correo',
		                                     'help_text' => 'Ingresa tu código o correo para recuperar la contraseña',
		));
	}
	
	public function clean_account () {
		$account = mb_strtolower (trim($this->cleaned_data['account']));
		
		$sql = new Gatuf_SQL ('correo=%s OR codigo=%s', array ($account, $account));
		$alumnos = Gatuf::factory('Calif_Alumno')->getList (array ('filter' => $sql->gen()));
		$maestros = Gatuf::factory('Calif_Maestro')->getList (array ('filter' => $sql->gen()));
		$usuarios = array_merge ($alumnos, $maestros);
		
		if (count ($usuarios) == 0) {
			throw new Gatuf_Form_Invalid ('Lo sentimos, no podemos encontrar un usuario con este código o correo. Por favor intentalo de nuevo');
		}
		$ok = false;
		foreach ($usuarios as $user) {
			if ($user->active) {
				$ok = true;
				continue;
			}
			
			$ok = false;
		}
		
		if (!$ok) {
			throw new Gatuf_Form_Invalid ('Lo sentimos, no podemos encontrar un usuario con este código o correo. Por favor intentalo de nuevo');
		}
		
		return $account;
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception ('No se puede guardar el formulario en un estado inválido');
		}
		
		$account = $this->cleaned_data['account'];
		
		$sql = new Gatuf_SQL ('correo=%s OR codigo=%s', array ($account, $account));
		$alumnos = Gatuf::factory('Calif_Alumno')->getList (array ('filter' => $sql->gen()));
		$maestros = Gatuf::factory('Calif_Maestro')->getList (array ('filter' => $sql->gen()));
		$usuarios = array_merge ($alumnos, $maestros);
		
		$return_url = '';
		foreach ($usuarios as $user) {
			if ($user->active) {
				$return_url = Gatuf_HTTP_URL_urlForView ('Calif_Views::passwordRecoveryInputCode');
				$tmpl = new Gatuf_Template('calif/user/recuperarcontra-email.txt');
				$cr = new Gatuf_Crypt (md5(Gatuf::config('secret_key')));
				$code = trim ($cr->encrypt($user->correo.':'.$user->codigo.':'.time()), '~');
				$code = substr (md5 (Gatuf::config ('secret_key').$code), 0, 2).$code;
				$url = Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Calif_Views::passwordRecovery', array ($code), array (), false);
				$urlic = Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Calif_Views::passwordRecoveryInputCode', array (), array (), false);
				$context = new Gatuf_Template_Context (
				               array ('url' => Gatuf_Template::markSafe ($url),
				                      'urlik' => Gatuf_Template::markSafe ($urlic),
				                      'user' => $user,
				                      'key' => Gatuf_Template::markSafe ($code)));
				$email = new Gatuf_Mail (Gatuf::config ('from_email'), $user->correo, 'Recuperar contraseña - Sistema de calificaciones');
				$email->setReturnPath (Gatuf::config ('bounce_email', Gatuf::config ('from_email')));
				$email->addTextMessage ($tmpl->render ($context));
				$email->sendMail ();
			}
		}
		return $return_url;
	}
}