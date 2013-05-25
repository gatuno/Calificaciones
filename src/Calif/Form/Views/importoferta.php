<?php

class Calif_Form_Views_importoferta extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['oferta'] = new Gatuf_Form_Field_File (
			array('label' => 'Seleccionar archivo',
				'help_text' => 'Su archivo separado por comas. (Estilo Monica Durón)',
				'move_function_params' => array(),
				'max_size' => 10485760,
				'move_function' => 'Calif_Utils_importoferta'
		));
	}
}
