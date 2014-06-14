<?php

class Calif_Salon extends Gatuf_Model {
	/* Manejador de la tabla salones */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'salones';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'edificio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Calif_Edificio',
			),
			'aula' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 10,
			),
			'cupo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 0,
			),
		);
		
		$this->default_order = 'edificio ASC, aula ASC';
	}
	
	function getSalon ($edificio, $aula) {
		$req = sprintf ('SELECT * FROM %s WHERE edificio = %s AND aula = %s', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($edificio, $this->_con), Gatuf_DB_IdentityToDb ($aula, $this->_con));
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception($this->_con->getError ());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		$this->_reset ();
		foreach ($this->_a['cols'] as $col => $val) {
			if (isset($rs[0][$col])) $this->_data[$col] = $this->_fromDb($rs[0][$col], $col);
		}
		$this->restore ();
		return true;
	}
	
	public function displaylinkedaula ($extra = null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Edificio::verEdificio', array ($this->edificio)).'#salon_'.$this->id.'">'.$this->aula.'</a>';
	}
	
	public function displaylinkededificio ($extra = null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Edificio::verEdificio', array ($this->edificio)).'">'.$this->edificio.'</a>';
	}
	
	function __toString () {
		return $this->edificio.' - '.$this->aula;
	}
}
