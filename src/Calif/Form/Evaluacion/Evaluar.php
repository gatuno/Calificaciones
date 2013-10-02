<?php

class Calif_Form_Evaluacion_Evaluar extends Gatuf_Form {
	
	public function initFields($extra=array()) {
		
		$choices = array();
		//var_dump ($extra['eval']);
		foreach($extra['eval'] as $eval){
			foreach($eval as $key => $e){
				$choices[$e] = $key; 
			}
		}
		
		$this->fields['modo_eval'] = new Gatuf_Form_Field_Varchar (
		array (
			'required' => true,
			'label' => 'Modo a Evaluar',
			'initial' => '',
			'widget_attrs' => array (
				'choices' => $choices,
			),
			'widget' => 'Gatuf_Form_Widget_SelectInput',
			));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		return $this->cleaned_data['modo_eval'];

	}
}
