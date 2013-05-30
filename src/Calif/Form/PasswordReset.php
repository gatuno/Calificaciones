<?php

Gatuf::loadFunction ('Gatuf_HTTP_URL_urlForView');

class Calif_Form_PasswordReset extends Gatuf_Form {
	protected $user = null;
	
	public function initFields($extra=array()) {
		$this->user = $extra['user'];
		$this->fields['key'] = new Gatuf_Form_Field_Varchar(
		                           array('required' => true,
		                           'label' => 'Tu código de verificación',
		                           'initial' => $extra['key'],
		                           'widget' => 'Gatuf_Form_Widget_HiddenInput',
		));
		$this->fields['password'] = new Gatuf_Form_Field_Varchar(
		                           array('required' => true,
		                           'label' => 'Tu contraseña',
		                           'initial' => '',
		                           'widget' => 'Gatuf_Form_Widget_PasswordInput',
		                           'help_text' => 'Tu contraseña debe ser díficil de encontrar para otras personas.',
		                           'widget_attrs' => array(
		                                             'maxlength' => 50,
		                                             'size' => 15,
		                           ),
		));
		$this->fields['password2'] = new Gatuf_Form_Field_Varchar(
		                           array('required' => true,
		                           'label' => 'Confirma tu contraseña',
		                           'initial' => '',
		                           'widget' => 'Gatuf_Form_Widget_PasswordInput',
		                           'widget_attrs' => array(
		                                             'maxlength' => 50,
		                                             'size' => 15,
		                           ),
		));
	}
	
	public function clean () {
		if ($this->cleaned_data['password'] != $this->cleaned_data['password2']) {
			throw new Gatuf_Form_Invalid ('Las dos contraseñas deben ser la misma');
		}
		if (!$this->user->active) {
			throw new Gatuf_Form_Invalid ('Esta cuenta no está activa. Por favor contacta al administrador');
		}
		
		return $this->cleaned_data;
	}
	
	public function clean_key () {
		$this->cleaned_data ['key'] = trim ($this->cleaned_data['key']);
		
		$error = 'La código de verificación no es válido. Prueba a copiarlo y pegarlo directamente desde el correo de verificación';
		if (false === ($cres = self::checkKeyHash ($this->cleaned_data['key']))) {
			throw new Gatuf_Form_Invalid ($error);
		}
		
		$guser = Gatuf::factory ('Calif_User')->getUser ($cres[1]);
		if ($guser === false || $guser->correo != $cres[0]) {
			throw new Gatuf_Form_Invalid ($error);
		}
		
		if ((time() - $cres[2]) > 86400) {
			throw new Gatuf_Form_Invalid ('Lo sentimos, el código de verificación ha expirado, por favor intentalo de nuevo. Por razones de seguridad, los códigos de verificación son sólo válidas por 24 horas');
		}
		return $this->cleaned_data['key'];
	}
	
	function save($commit=true) {
		if (!$this->isValid()) {
			throw new Exception ('Cannot save an invalid form');
		}
		
		$this->user->setPassword ($this->cleaned_data['password']);
		$this->user->updateSession ();
		
		return $this->user;
	}
	
	public static function checkKeyHash ($key) {
		$hash = substr ($key, 0, 2);
		$encrypted = substr ($key, 2);
		if ($hash != substr(md5(Gatuf::config('secret_key').$encrypted), 0, 2)) {
			return false;
		}
		$cr = new Gatuf_Crypt (md5(Gatuf::config('secret_key')));
		$f = explode (':', $cr->decrypt($encrypted), 3);
		if (count ($f) != 3) {
			return false;
		}
		return $f;
	}
}
