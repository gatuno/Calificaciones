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
			'grupo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
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
			       'default' => 1,
			),
			'apertura' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => true,
			       'is_null' => true,
			       'default' => null,
			),
			'cierre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => true,
			       'is_null' => true,
			       'default' => null,
			),
		);
		$this->default_order = 'evaluacion ASC';
		
		Gatuf::loadFunction ('Calif_Calendario_getDefault');
		$this->_a['calpfx'] = Calif_Calendario_getDefault ();
	}
	
	public function getGroupSum ($materia, $grupo) {
		$sql = new Gatuf_SQL ('materia=%s AND grupo=%s', array ($materia, $grupo));
		
		$req = sprintf ('SELECT grupo, SUM(porcentaje) AS sum FROM %s WHERE %s GROUP BY (grupo)', $this->getSqlTable (), $sql->gen ());
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return 0;
		}
		return (int) $rs[0]['sum'];
	}
	
	public function preSave ($create = false) {
		$eval = new Calif_Evaluacion ($this->evaluacion);
		$this->grupo = $eval->grupo;
	}
}
