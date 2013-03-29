<?php

class Calif_Form_Carrera_Actualizar extends Gatuf_Form {
	public $carrera;
	
	public function initFields($extra=array()) {
		$this->carrera = $extra['carrera'];
		$this->fields['clave'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => false,
				'label' => 'Clave',
				'initial' => $this->carrera->clave,
				'help_text' => 'Debe ser una clave de carrera única',
				'widget_attrs' => array(
					'max_length' => 5,
					'min_length' => 3,
					'readonly' => 'readonly',
				),
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Descripción',
				'initial' => $this->carrera->descripcion,
				'help_text' => 'Una descripción como Ingeniería en Computación',
				'widget_attrs' => array(
					'max_length' => 100,
					'size' => 30,
				),
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->carrera->descripcion = $this->cleaned_data['descripcion'];
		
		$this->carrera->update();
		
		return $this->carrera;
	}
}
