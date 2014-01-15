<?php

class Calif_Form_System_AuditoriaSeveraCarrera extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['archivo'] = new Gatuf_Form_Field_File (
			array (
				'required' => true,
				'label' => 'Archivo',
				'help_text' => 'Su oferta académica de Siiau',
				'move_function' => 'Calif_Utils_dontmove',
				'move_function_params' => array(),
				'max_size' => 10485760,
		));
		
		$choices = array ();
		
		foreach (Gatuf::factory ('Calif_Carrera')->getList () as $carrera) {
			$choices[$carrera->descripcion] = $carrera->clave;
		}
		
		$this->fields['carrera'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'initial' => -1,
				'label' => 'Oferta de la carrera',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		));
	}
	
	public function save ($commit = true) {
		$res = array ('car' => $this->cleaned_data['carrera'], 'archivo' => $this->data['archivo']['tmp_name']);
		
		return $res;
	}
}
