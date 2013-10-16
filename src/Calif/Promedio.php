<?php

class Calif_Promedio extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'promedios';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'nrc' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Calif_Seccion',
			       'blank' => false,
			),
			'evaluacion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Calif_Evaluacion',
			       'blank' => false,
			),
			'promedio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Float',
			       'max_digits' => '5',
			       'decimal_places' => '2',
			       'blank' => false,
			       'editable' => false,
			),
		);
		
		$this->_a['idx'] = array (
			'promedios_idx' =>
			array (
			       'col' => 'nrc, evaluacion',
			       'type' => 'index',
			),
		);
	}
	
	public function getPromedioEval ($nrc, $eval) {
		$req = sprintf ('SELECT * FROM %s WHERE nrc=%s AND evaluacion=%s', $this->getSqlTable (), Gatuf_DB_IntegerToDb ($nrc, $this->_con), Gatuf_DB_IntegerToDb ($eval, $this->_con));
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		
		$this->_reset ();
		foreach ($this->_a['cols'] as $col => $val) {
			if (isset($rs[0][$col])) $this->_data[$col] = $this->_fromDb($rs[0][$col], $col);
		}
		
		$this->restore ();
	}
}
