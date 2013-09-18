<?php

class Calif_Form_Evaluacion_EvaluarAlumno extends Gatuf_Form {
	public function initFields($extra=array()) {
		$gruposeval = $extra['evaluacion'];
		$calificacion = $extra['calificacion'];
		foreach ($gruposeval as $key => $descrip) {
				$this->fields[$descrip] = new Gatuf_Form_Field_Varchar(
				array(
				'label' => $descrip,
				'initial' => $calificacion [$key],
				'max_length' => 6,
				'widget_attrs' => array(
					'maxlength' => 6,
					),
				));
			}
		}
		
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		return 0;
	}
}
