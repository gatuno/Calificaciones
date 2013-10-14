<?php

class Calif_Form_Maestro_Agregar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$this->fields['codigo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Código',
				'initial' => '',
				'help_text' => 'El código del maestro de 7 caracteres',
				'min' => 1000000,
				'max' => 9999999,
				'widget_attrs' => array (
					'maxlength' => 7,
					'size' => 12,
				),
		));
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => '',
				'help_text' => 'El nombre o nombres del maestro',
				'max_length' => 50,
				'min_length' => 5,
				'widget_attrs' => array (
					'maxlength' => 50,
					'size' => 30,
				),
		));
		
		$this->fields['apellido'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Apellido',
				'initial' => '',
				'help_text' => 'Los apellidos del maestro',
				'max_length' => 100,
				'min_length' => 5,
				'widget_attrs' => array (
					'maxlength' => 100,
					'size' => 30,
				),
		));
		
		$this->fields['correo'] = new Gatuf_Form_Field_Email (
			array (
				'required' => true,
				'label' => 'Correo',
				'initial' => '',
				'help_text' => 'Un correo',
		));
	}
	
	public function clean_codigo () {
		$codigo = $this->cleaned_data['codigo'];
		$sql = new Gatuf_SQL ('codigo=%s', array ($codigo));
		$l = Gatuf::factory('Calif_Maestro')->getList(array ('filter' => $sql->gen(), 'count' => true));
		
		if ($l > 0) {
			throw new Gatuf_Form_Invalid (sprintf ('El código \'<a href="%s">%s</a>\' del maestro especificado ya existe', Gatuf_HTTP_URL_urlForView('Calif_Views_Maestro::verMaestro', array ($codigo)), $codigo));
		}
		
		return $codigo;
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the model from and invalid form.');
		}
		
		$maestro = new Calif_Maestro ();
		$user = new Calif_User ();
		
		$maestro->codigo = $this->cleaned_data['codigo'];
		$maestro->nombre = $this->cleaned_data['nombre'];
		$maestro->apellido = $this->cleaned_data['apellido'];
		$user->login = $this->cleaned_data['codigo'];
		$user->email = $this->cleaned_data['correo'];
		$user->type = 'm';
		$user->administrator = false;
		
		$maestro->user = $user;
		
		if ($commit) {
			$maestro->create();
			$user->create ();
		}
		
		return $maestro;
	}
}
