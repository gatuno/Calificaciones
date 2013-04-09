<?php

class Calif_Form_Materia_Actualizar extends Gatuf_Form {
	public $materia;
	
	public function initFields($extra=array()) {
		$this->materia = $extra['materia'];
		$this->fields['clave'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => false,
				'label' => 'Clave',
				'initial' => $this->materia->clave,
				'help_text' => 'La clave de la materia',
				'max_length' => 5,
				'min_length' => 5,
				'widget_attrs' => array(
					'maxlength' => 5,
					'readonly' => 'readonly',
				),
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Materia',
				'initial' => $this->materia->descripcion,
				'help_text' => 'El nombre completo de la materia',
				'max_length' => 100,
				'widget_attrs' => array(
					'maxlength' => 100,
					'size' => 30,
				),
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->materia->descripcion = $this->cleaned_data['descripcion'];
		
		$this->materia->update();
		
		return $this->materia;
	}
}
