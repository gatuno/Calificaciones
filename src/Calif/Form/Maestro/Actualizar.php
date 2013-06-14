<?php

class Calif_Form_Maestro_Actualizar extends Gatuf_Form {
	public $maestro;
	
	public function initFields ($extra = array ()) {
		$this->maestro = $extra['maestro'];
		$this->fields['codigo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => false,
				'label' => 'Código',
				'initial' => $this->maestro->codigo,
				'help_text' => 'El código del maestro de 7 caracteres',
				'min' => 1000000,
				'max' => 9999999,
				'widget_attrs' => array (
					'maxlength' => 7,
					'size' => 12,
					'readonly' => 'readonly',
				),
		));
		
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
				'initial' => $this->maestro->correo,
				'help_text' => 'Un correo',
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the model from and invalid form.');
		}
		
		$this->maestro->nombre = $this->cleaned_data['nombre'];
		$this->maestro->apellido = $this->cleaned_data['apellido'];
		$this->maestro->correo = $this->cleaned_data['correo'];
		
		$this->maestro->update();
		
		return $this->maestro;
	}
}
