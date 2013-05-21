<?php

class Calif_Materia extends Gatuf_Model {
	/* Manejador de la tabla de carreras */
	
	/* Campos */
	public $clave;
	public $descripcion;
	
	public $misevals;
	
	public $tabla_porcentajes;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Materias';
		$this->tabla_porcentajes = 'Porcentajes';
		$this->misevals = array ();
	}
	
	function getPorcentajesSqlTable () {
		return $this->_con->pfx.$this->tabla_porcentajes;
	}
	
	function getMateria ($clave) {
		/* Recuperar una carrera */
		$req = sprintf ('SELECT * FROM %s WHERE clave = %s', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($clave, $this->_con));
		
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
	
	public function getEvals ($grupo = null) {
		$eval = new Calif_Evaluacion ();
		
		$sql_filter = new Gatuf_SQL ('materia=%s', $this->clave);
		if (!is_null ($grupo)) {
			$sql_filter->SAnd ($grupo);
		}
		
		$req = sprintf ('SELECT P.evaluacion, P.porcentaje, P.abierto, P.apertura, P.cierre, E.descripcion FROM %s AS P INNER JOIN %s AS E ON P.evaluacion = E.id WHERE %s', $this->getPorcentajesSqlTable (), $eval->getSqlTable(), $sql_filter->gen());
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return array ();
		}
		
		return $rs;
	}
	
	public function getNotEvals ($grupo = null, $count = false) {
		/* Super SQL:
		 SELECT * FROM Evaluaciones AS E WHERE NOT EXISTS (SELECT * FROM Porcentajes AS P WHERE P.Materia = 'CC100' AND E.Id = P.Evaluacion) AND Grupo = 2 */
		$eval = new Calif_Evaluacion ();
		$filtro = new Gatuf_SQL ('NOT EXISTS (SELECT * FROM '.$this->getPorcentajesSqlTable().' WHERE '.$this->getPorcentajesSqlTable().'.Materia=%s AND '.$this->getPorcentajesSqlTable().'.Evaluacion='.$eval->getSqlTable().'.Id)', $this->clave);
		
		if (!is_null ($grupo)) {
			$filtro->SAnd ($grupo);
		}
		
		$req = sprintf ('SELECT * FROM %s WHERE %s', $eval->getSqlTable(), $filtro->gen());
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception ($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			if ($count == true) return false;
			return array ();
		}
		
		if ($count == true) return true;
		$res = array ();
		foreach ($rs as $row) {
			$eval->getEval ($row['id']);
			$res[] = clone ($eval);
		}
		
		return $res;
	}
	
	function create () {
		$req = sprintf ('INSERT INTO %s (clave, descripcion) VALUES (%s, %s);', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->clave, $this->_con), Gatuf_DB_IdentityToDb ($this->descripcion, $this->_con));
		$this->_con->execute($req);
		
		return true;
	}
	
	function update () {
		$req = sprintf ('UPDATE %s SET descripcion = %s WHERE clave = %s', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->descripcion, $this->_con), Gatuf_DB_IdentityToDb ($this->clave, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	public function addEval ($eval, $porcentaje) {
		/* Agregar una forma de evaluación a esta materia */
		$req = sprintf ('INSERT INTO %s (Materia, Evaluacion, Porcentaje) VALUES (%s, %s, %s)', $this->getPorcentajesSqlTable(), Gatuf_DB_IdentityToDb ($this->clave, $this->_con), Gatuf_DB_IntegerToDb ($eval, $this->_con), Gatuf_DB_IntegerToDb ($porcentaje, $this->_con));
		$this->_con->execute($req);
		
		return true;
	}
	
	public function displaylinkedclave ($extra) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::verMateria', array ($this->clave)).'">'.$this->clave.'</a>';
	}
}
