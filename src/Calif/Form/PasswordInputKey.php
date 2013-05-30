<?php

Gatuf::loadFunction ('Gatuf_HTTP_URL_urlForView');

class Calif_Form_PasswordInputKey extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['key'] = new Gatuf_Form_Field_Varchar(
		                               array('required' => true,
		                                     'label' => 'El código de verificación',
		                                     'initial' => '',
		                                     'widget_attrs' => array (
		                                         'size' => 50,
		                                     ),
		));
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
		
		if ((time() - $cres[2]) > 10800) {
			throw new Gatuf_Form_Invalid ('Lo sentimos, el código de verificación ha expirado, por favor intentalo de nuevo. Por razones de seguridad, los códigos de verificación son sólo válidas por 3 horas');
		}
		return $this->cleaned_data['key'];
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save an invalid form.');
		}
		return Gatuf_HTTP_URL_urlForView ('Calif_Views::passwordRecovery', array ($this->cleaned_data['key']));
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
