<?php

class Calif_Form_Maestro_Actualizar extends Gatuf_Form {
	public $maestro;
	
	public function initFields ($extra = array ()) {
		$this->maestro = $extra['maestro'];
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => $this->maestro->nombre,
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
				'initial' => $this->maestro->apellido,
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
				'initial' => $this->maestro->user->email,
				'help_text' => 'Un correo',
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the model from and invalid form.');
		}
		
		$this->maestro->nombre = $this->cleaned_data['nombre'];
		$this->maestro->apellido = $this->cleaned_data['apellido'];
		$this->maestro->user->email = $this->cleaned_data['correo'];
		
		if ($commit) {
			$this->maestro->update();
			$this->maestro->user->update ();
		}
		return $this->maestro;
	}
}
