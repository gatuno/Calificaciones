<?php

class Calif_Edificio extends Gatuf_Model {
	/* Manejador de la tabla de edificios */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'edificios';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'clave';
		
		$this->_a['cols'] = array (
			'clave' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 10,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 10,
			),
		);
		
	}
	
	function displaylinkedclave () {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Edificio::verEdificio', array ($this->clave)).'">'.$this->clave.'</a>';
	}
}
