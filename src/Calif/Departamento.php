<?php

class Calif_Departamento extends Gatuf_Model {
	/* Manejador de la tabla de departamentos */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'departamentos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'clave';
		
		$this->_a['cols'] = array (
			'clave' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 100,
			),
		);
	}
}
