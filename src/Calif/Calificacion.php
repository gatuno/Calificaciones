<?php

class Calif_Calificacion extends Gatuf_Model {
	/* Manejador de la tabla de calificaciones */
	
	/* Campos */
	public $id;
	public $nrc;
	public $alumno;
	public $evaluacion;
	public $valor;
	
	function __construct () {
		$this->_getConnection();
		$this->tabla = 'Calificaciones';
	}
		
	public function getCalif ($filtro) {
		$req = sprintf ('SELECT * FROM %s WHERE %s', $this->getSqlTable(), $filtro);
			if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		foreach ($rs[0] as $col => $val) {
			$this->$col = $val;
		}
		return true;
	}
	
	function update () {
		$req = sprintf ('UPDATE %s SET valor = %s WHERE nrc = %s AND alumno= %s AND evaluacion = %s', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->valor, $this->_con), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con), Gatuf_DB_IdentityToDb ($this->alumno, $this->_con), Gatuf_DB_IntegerToDb ($this->evaluacion, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
}
