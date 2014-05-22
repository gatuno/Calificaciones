<?php

class Calif_Form_Calendario_SeleccionarTodos extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$choices = array ();
		
		foreach (Gatuf::factory ('Calif_Calendario')->getList () as $cal) {
			$choices[$cal->descripcion] = $cal->clave;
		}
		
		$this->fields['calendario'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Calendario',
				'initial' => Calif_Calendario_getDefault (),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the form in a valid state');
		}
		
		$calendario = new Calif_Calendario ($this->cleaned_data['calendario']);
		
		return $calendario;
	}
}
