<?php

class Calif_Evaluacion extends Gatuf_Model {
	/* Manejador de la tabla de evaluaciones */
	
	/* Campos */
	public $id;
	public $grupo;
	public $descripcion;
	public $grupodescripcion;
	
	public $tabla_grupos;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Evaluaciones';
		$this->tabla_grupos = 'Grupos_Evaluaciones';
	}
	
	function getGruposSqlTable () {
		return $this->_con->pfx.$this->tabla_grupos;
	}
	
	function getEval ($id) {
		/* Recuperar una evaluacion */
		$req = sprintf ('SELECT * FROM %s WHERE id = %s', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($id, $this->_con));
		
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
	
	function getGruposEvals () {
		$req = sprintf ('SELECT * FROM %s', $this->getGruposSqlTable());
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		
		$grupos_evals = array ();
		foreach ($rs as $row) {
			$grupos_evals[$row['id']] = $row['descripcion'];
		}
		
		return $grupos_evals;
	}
	
	function getGrupoEval ($id) {
		$where = new Gatuf_SQL ('id=%s', $id);
		$req = sprintf ('SELECT * FROM %s WHERE %s', $this->getGruposSqlTable(), $where->gen());
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		
		return $rs[0]['descripcion'];
	}
	
	function create () {
		$req = sprintf ('INSERT INTO %s (grupo, descripcion) VALUES (%s, %s);', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->grupo, $this->_con), Gatuf_DB_IdentityToDb ($this->descripcion, $this->_con));
		$this->_con->execute($req);
		
		$this->id = $this->_con->getLastId ();
		
		return true;
	}
	
	function update () {
		$req = sprintf ('UPDATE %s SET descripcion = %s WHERE id = %s', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->descripcion, $this->_con), Gatuf_IntegerToDb ($this->id));
		
		$this->_con->execute($req);
		
		return true;
	}
}
