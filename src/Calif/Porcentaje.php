<?php

class Calif_Porcentaje extends Gatuf_Model {
	/* Controlador de la tabla de porcentajes */
	
	/* Campos */
	public $id;
	public $materia;
	public $evaluacion;
	public $grupo;
	public $porcentaje;
	public $abierto;
	public $apertura, $cierre;
	
	public function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Porcentajes';
		$this->abierto = false;
		$this->apertura = null;
		$this->cierre = null;
	}
	
	public function create () {
		$req = sprintf ('INSERT INTO %s (materia, evaluacion, grupo, porcentaje, abierto, apertura, cierre) VALUES (%s, %s, %s, %s, %s, %s, %s)', $this->getSqlTable (), Gatuf_DB_IdentityToDb ($this->materia, $this->_con), Gatuf_DB_IntegerToDb ($this->evaluacion, $this->_con), Gatuf_DB_IntegerToDb ($this->grupo, $this->_con), Gatuf_DB_IntegerToDb ($this->porcentaje, $this->_con), Gatuf_DB_BooleanToDb ($this->abierto, $this->_con), Gatuf_DB_IdentityToDb ($this->apertura, $this->_con), Gatuf_DB_IdentityToDb ($this->cierre, $this->_con));
		
		$this->_con->execute ($req);
		
		$this->id = $this->_con->getLastId ();
		
		return true;
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
}
