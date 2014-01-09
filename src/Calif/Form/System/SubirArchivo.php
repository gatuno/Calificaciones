<?php

class Calif_Form_System_SubirArchivo extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['archivo'] = new Gatuf_Form_Field_File (
			array (
				'required' => true,
				'label' => 'Archivo',
				'help_text' => 'El archivo a subir',
				'move_function' => 'Calif_Utils_dontmove',
				'move_function_params' => array(),
				'max_size' => 10485760,
		));
	}
	
	public function save ($commit = true) {
		return $this->data['archivo']['tmp_name'];
	}
}
