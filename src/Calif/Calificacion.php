<?php

class Calif_Calificacion extends Gatuf_Model {
	/* Manejador de la tabla de calificaciones */
	
	/* Campos */
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
	
	public function getPromedio ($grupo_eval) {
		$req = sprintf ('SELECT C.alumno, C.nrc, P.grupo, SUM(GREATEST (IFNULL(C.valor,0),0) * P.porcentaje / 100) AS suma FROM %s AS C INNER JOIN %s AS S ON C.nrc = S.nrc INNER JOIN %s AS P ON S.materia = P.materia AND C.evaluacion = P.evaluacion WHERE C.Alumno = %s AND C.nrc = %s AND P.grupo = %s GROUP BY P.grupo', $this->getSqlTable (), Gatuf::factory ('Calif_Seccion')->getSqlTable (), Gatuf::factory('Calif_Porcentaje')->getSqlTable (), Gatuf_DB_IdentityToDb ($this->alumno, $this->_con), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con), Gatuf_DB_IntegerToDb ($grupo_eval, $this->_con));
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		
		return $rs[0]['suma'];
	}
	
		public function getPromedioEval ($eval) {
		$req = sprintf ('SELECT * FROM Promedios_Evaluaciones WHERE seccion = %s AND evaluacion = %s', Gatuf_DB_IntegerToDb ($this->nrc, $this->_con), Gatuf_DB_IntegerToDb ($eval, $this->_con));
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		
		return $rs[0]['promedio'];
	}
}
