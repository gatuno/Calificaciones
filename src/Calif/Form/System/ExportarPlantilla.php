<?php

class Calif_Form_System_ExportarPlantilla extends Gatuf_Form {
	public function initFields($extra=array()) {
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
		
		$choices_dep = array ();
		
		foreach (Gatuf::factory ('Calif_Departamento')->getList () as $dep) {
			$choices_dep[$dep->descripcion] = $dep->clave;
		}
		
		$this->fields['departamento'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Departamento',
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices_dep,
				),
		));
		
		$this->fields['archivo'] = new Gatuf_Form_Field_File (
			array (
				'required' => true,
				'label' => 'Archivo',
				'help_text' => 'Su plantilla separada por comas, desde RH',
				'move_function' => 'Calif_Utils_dontmove',
				'move_function_params' => array(),
				'max_size' => 10485760,
		));
	}
	
	public function save ($commit = true) {
		$res = array ('cal' => $this->cleaned_data['calendario'], 'archivo' => $this->data['archivo']['tmp_name'], 'dep' => $this->cleaned_data['departamento']);
		
		return $res;
	}
}
