<?php

class Calif_Nombramiento extends Gatuf_Model {
	/* Manejador de la tabla de nombramientos */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'nombramientos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'clave';
		
		$this->_a['cols'] = array (
			'clave' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 5,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 100,
			),
			'horas' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
		);
	}
}
