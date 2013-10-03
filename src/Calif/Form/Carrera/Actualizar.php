<?php

class Calif_Form_Carrera_Actualizar extends Gatuf_Form {
	public $carrera;
	
	public function initFields($extra=array()) {
		$this->carrera = $extra['carrera'];
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Descripción',
				'initial' => $this->carrera->descripcion,
				'help_text' => 'Una descripción como Ingeniería en Computación',
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
		
		$this->carrera->descripcion = $this->cleaned_data['descripcion'];
		
		$this->carrera->update();
		
		return $this->carrera;
	}
}
