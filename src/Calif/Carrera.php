<?php

class Calif_Carrera extends Gatuf_Model {
	/* Manejador de la tabla de carreras */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'carreras';
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
			       'size' => 120,
			),
			'color' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 0,
			),
		);
		$this->default_order = 'clave ASC, descripcion ASC';
	}
}
