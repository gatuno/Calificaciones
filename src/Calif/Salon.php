<?php

class Calif_Salon extends Gatuf_Model {
	/* Manejador de la tabla salones */
	public $_model = __CLASS__;
	
	/* Mis campos */
	public $id;
	public $edificio;
	public $aula;
	public $cupo;
	
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
		foreach ($rs[0] as $col => $val) {
			$this->$col = $val;
		}
		return true;
	}
	
	public function displaylinkedaula ($extra) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::verSalon', array ($this->id)).'">'.$this->aula.'</a>';
	}
	
	public function displaylinkededificio ($extra) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Edificio::verEdificio', array ($this->edificio)).'">'.$this->edificio.'</a>';
	}
}
