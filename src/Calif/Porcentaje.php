<?php

class Calif_Porcentaje extends Gatuf_Model {
	/* Manejador de la tabla de porcentajes */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'porcentajes';
		$this->_a['model'] = __CLASS__;
		$this->_primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'materia' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Calif_Materia',
			),
			'evaluacion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Calif_Evaluacion',
			),
			'porcentaje' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 1
			),
			'abierto' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => true,
			       'is_null' => 1,
			       'default' => 1,
			),
			'apertura' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => true,
			       'is_null' => 1,
			),
			'cierre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => true,
			       'is_null' => 1,
			),
		);
	}
}
