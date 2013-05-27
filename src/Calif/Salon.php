<?php

class Calif_Salon extends Gatuf_Model {
	/* Manejador de la tabla salones */
	
	/* Mis campos */
	public $id;
	public $edificio;
	public $aula;
	public $cupo;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Salones';
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
	
	function getSalonById ($id) {
		$req = sprintf ('SELECT * FROM %s WHERE id = %s', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($id, $this->_con));
		
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
	
	function create () {
		$req = sprintf ('INSERT INTO %s (edificio, aula, cupo) VALUES (%s, %s, %s);', $this->getSqlTable (), Gatuf_DB_IdentityToDb ($this->edificio, $this->_con), Gatuf_DB_IdentityToDb ($this->aula, $this->_con), Gatuf_DB_IntegerToDb ($this->cupo, $this->_con));
		$this->_con->execute ($req);
		
		$this->id = $this->_con->getLastId();
		
		return true;
	}
	
	function update () {
		$req = sprintf ('UPDATE %s SET cupo = %s WHERE id = %s', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->cupo, $this->_con));
		
		$this->_con->execute ($req);
		
		return true;
	}
	
	public function displaylinkedaula ($extra) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::verSalon', array ($this->id)).'">'.$this->aula.'</a>';
	}
	
	public function displaylinkededificio ($extra) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Edificio::verEdificio', array ($this->edificio)).'">'.$this->edificio.'</a>';
	}
}
