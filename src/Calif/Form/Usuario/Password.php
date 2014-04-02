<?php

class Calif_Form_Usuario_Password extends Gatuf_Form {
	public $usuario;
	
	public function initFields ($extra = array ()) {
		$this->user = $extra['usuario'];

		$this->fields['actual'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Actual',
				'help_text' => 'La contraseña actual',
				'max_length' => 50,
				'widget' => 'Gatuf_Form_Widget_PasswordInput',
				'widget_attrs' => array (
					'maxlength' => 50,
					'size' => 30,
				),
		));
		
		$this->fields['nueva'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nueva',
				'help_text' => 'La nueva contraseña',
				'max_length' => 50,
				'widget' => 'Gatuf_Form_Widget_PasswordInput',
				'widget_attrs' => array (
					'maxlength' => 50,
					'size' => 30,
				),
		));

		$this->fields['repite'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Repite Nueva',
				'help_text' => 'La nueva contraseña de nuevo',
				'max_length' => 50,
				'widget' => 'Gatuf_Form_Widget_PasswordInput',
				'widget_attrs' => array (
					'maxlength' => 50,
					'size' => 30,
				),
		));
	}
	
	public function clean () {
		$actual = $this->cleaned_data['actual'];
		$nueva = $this->cleaned_data['nueva'];
		$repite = $this->cleaned_data['repite'];
		
		if (!$this->user->checkPassword($actual) ) {
			throw new Gatuf_Form_Invalid ('La contraseña es incorrecta');
		}
		
		if ( $nueva != $repite) {
			throw new Gatuf_Form_Invalid ('Los campos de contraseña nueva no coinciden');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Verifique que se han escrito correctamente las contraseñas');
		}
		
		$this->user->setPassword($this->cleaned_data['nueva']);
		
		if ($commit) {
			$this->user->update();
		}
		return $this->user;
	}
}
