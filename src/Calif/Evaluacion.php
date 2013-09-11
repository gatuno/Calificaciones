<?php

class Calif_Evaluacion extends Gatuf_Model {
	/* Manejador de la tabla de evaluaciones */
	
	/* Campos */
	public $id;
	public $grupo;
	public $descripcion;
	public $grupo_desc;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Evaluaciones';
		$this->tabla_view = 'Evaluaciones_View';
		$this->tabla_grupos = 'Grupos_Evaluaciones';
		$this->default_order = 'grupo ASC, descripcion ASC';
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
