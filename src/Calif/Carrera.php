<?php

class Calif_Carrera extends Pluf_Model {
	public $_model = __CLASS__;
	function init () {
		# La tabla donde se guardan las carreras
		$this->_a['table'] = 'Carrera';
		$this->_a['model'] = 'Calif_Carrera';
		
		# Los campos
		$this->_a['cols'] = array (
			# La clave de la carrera
			'id' =>
			array (
				'type' => 'Pluf_DB_Field_Sequence',
				'blank' => true,
			),
			'clave' =>
			array (
				'type' => 'Pluf_DB_Field_Varchar',
				'blank' => false,
				'size' => 5,
				'verbose' => 'Clave de la carrera',
			),
			# La descripción de la carrera
			'descripcion' =>
			array (
				'type' => 'Pluf_DB_Field_Varchar',
				'blank' => false,
				'size' => 100,
				'verbose' => 'Nombre de la carrera',
			),
		);
	}
}
