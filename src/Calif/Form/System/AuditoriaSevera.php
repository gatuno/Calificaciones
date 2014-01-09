<?php

class Calif_Form_System_AuditoriaSevera extends Gatuf_Form {
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
		
		$choices = array ('Todos los departamentos' => -1);
		
		foreach (Gatuf::factory ('Calif_Departamento')->getList () as $departamento) {
			$choices[$departamento->descripcion] = $departamento->clave;
		}
		
		$this->fields['departamento'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'initial' => -1,
				'label' => 'Departamento',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		));
	}
	
	public function save ($commit = true) {
		$res = array ('dep' => $this->cleaned_data['departamento'], 'archivo' => $this->data['archivo']['tmp_name']);
		
		return $res;
	}
}
