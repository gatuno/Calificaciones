<?php

class Calif_NumeroPuesto extends Gatuf_Model {
	/* Manejador de la tabla de numero de puesto */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'numero_puestos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'numero';
		
		$this->_a['cols'] = array (
			'numero' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'nrc' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Calif_Seccion',
			),
			'carga' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			       'default' => 'a', /* Asignatura, Tiempo completo, Honorifico */
			),
			'tipo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			       'default' => 't',
			),
			'horas' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
		);
		
		$this->_a['idx'] = array (
			'tipo_idx' =>
			array (
			       'col' => 'tipo',
			       'type' => 'index',
			),
			'carga_idx' =>
			array (
			       'col' => 'carga',
			       'type' => 'index',
			),
		);
	}
	
	function maxPuesto () {
		$req = sprintf ('SELECT MAX(numero) AS max_numero FROM %s', $this->getSqlTable ());
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		return (int) $rs[0]['max_numero'];
	}
}
