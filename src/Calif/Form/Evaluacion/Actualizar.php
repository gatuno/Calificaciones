<?php

class Calif_Form_Evaluacion_Actualizar extends Gatuf_Form {
	private $evaluacion;
	public function initFields ($extra = array()) {
		$this->evaluacion = $extra['evaluacion'];
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Evaluacion',
				'initial' => $this->evaluacion->descripcion,
				'help_text' => 'El nombre de su forma de evaluación',
				'max_length' => 100,
				'widget_attrs' => array(
					'maxlength' => 100,
					'size' => 30,
				),
		));
		
		$this->fields['maestro'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Subida por el profesor',
				'initial' => $this->evaluacion->maestro,
				'help_text' => 'Indica si esta forma de evaluación es subida por el profesor',
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->evaluacion->descripcion = $this->cleaned_data['descripcion'];
		$this->evaluacion->maestro = $this->cleaned_data['maestro'];
		
		if ($commit) $this->evaluacion->update();
		
		return $this->evaluacion;
	}
}
