<?php

class Calif_Materia extends Gatuf_Model {
	/* Manejador de la tabla de materias */
	
	/* Campos */
	public $clave;
	public $descripcion;
	public $creditos;
	public $curso, $taller, $laboratorio, $seminario;
	
	/* El departamento, la llave foranea */
	public $departamento, $departamento_desc;
	/* La academia, la llave foranea */
	public $academia;
	
	/* Para las formas de evaluación */
	public $misevals;
	public $tabla_porcentajes;
	
	function __construct () {
		$this->_getConnection();
		$this->academia = null; /* FIXME: La academia */
		
		$this->tabla = 'Materias';
		$this->tabla_view = 'Materias_View';
		$this->tabla_porcentajes = 'Porcentajes';
		$this->misevals = array ();
		
		/* Relación N-M contra las carreras */
		$tabla = 'Catalogo_Carreras';
		
		$this->views['__catalogo_c__'] = array ();
		$this->views['__catalogo_c__']['tabla'] = $tabla;
		$this->views['__catalogo_c__']['join'] = ' LEFT JOIN '.$this->_con->pfx.$tabla.' ON '.$this->getSqlViewTable().'.clave='.$this->_con->pfx.$tabla.'.materia';
		$this->views['__catalogo_c__']['order'] = $this->default_order;
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
		$filtro = new Gatuf_SQL ('NOT EXISTS (SELECT * FROM '.$this->getPorcentajesSqlTable().' WHERE '.$this->getPorcentajesSqlTable().'.materia=%s AND '.$this->getPorcentajesSqlTable().'.evaluacion='.$eval->getSqlTable().'.Id)', $this->clave);
		
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
	
	public function getGroupSum ($grupo) {
		$eval = new Calif_Evaluacion ();
		
		$sql_filter = new Gatuf_SQL ('materia=%s', $this->clave);
		if (!is_null ($grupo)) {
			$sql_filter->SAnd ($grupo);
		}
		
		/* SELECT E.grupo, SUM(P.porcentaje) FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.evaluacion = E.id WHERE materia='I5882' GROUP BY E.grupo */
		$req = sprintf ('SELECT E.grupo, SUM(P.porcentaje) AS suma FROM %s AS P INNER JOIN %s AS E ON P.evaluacion = E.id WHERE %s GROUP BY (E.grupo)', $this->getPorcentajesSqlTable (), $eval->getSqlTable(), $sql_filter->gen());
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return array ();
		}
		
		return $rs[0];
	}
	
	function create () {
		$req = sprintf ('INSERT INTO %s (clave, descripcion, creditos, curso, taller, laboratorio, seminario, departamento) VALUES (%s, %s, %s, %s, %s, %s, %s, %s);', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->clave, $this->_con), Gatuf_DB_IdentityToDb ($this->descripcion, $this->_con), Gatuf_DB_IntegerToDb ($this->creditos, $this->_con), Gatuf_DB_BooleanToDb ($this->curso, $this->_con), Gatuf_DB_BooleanToDb ($this->taller, $this->_con), Gatuf_DB_BooleanToDb ($this->laboratorio, $this->_con), Gatuf_DB_BooleanToDb ($this->seminario, $this->_con), Gatuf_DB_IntegerToDb ($this->departamento, $this->_con));
		$this->_con->execute($req);
		
		return true;
	}
	
	function update () {
		$req = sprintf ('UPDATE %s SET descripcion = %s, departamento = %s, creditos = %s, curso = %s, taller = %s, laboratorio = %s, seminario = %s WHERE clave = %s', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->descripcion, $this->_con), Gatuf_DB_IntegerToDb ($this->departamento, $this->_con), Gatuf_DB_IntegerToDb ($this->creditos, $this->_con), Gatuf_DB_BooleanToDb ($this->curso, $this->_con), Gatuf_DB_BooleanToDb ($this->taller, $this->_con), Gatuf_DB_BooleanToDb ($this->laboratorio, $this->_con), Gatuf_DB_BooleanToDb ($this->seminario, $this->_con), Gatuf_DB_IdentityToDb ($this->clave, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	public function addEval ($eval, $porcentaje) {
		/* Agregar una forma de evaluación a esta materia */
		$req = sprintf ('INSERT INTO %s (Materia, Evaluacion, Porcentaje) VALUES (%s, %s, %s)', $this->getPorcentajesSqlTable(), Gatuf_DB_IdentityToDb ($this->clave, $this->_con), Gatuf_DB_IntegerToDb ($eval, $this->_con), Gatuf_DB_IntegerToDb ($porcentaje, $this->_con));
		$this->_con->execute($req);
		
		return true;
	}
	
	public function getCarrerasList ($p = array ()) {
		$default = array('view' => null,
		                 'filter' => null,
		                 'order' => null,
		                 'start' => null,
		                 'nb' => null,
		                 'count' => false);
		$p = array_merge($default, $p);
		
		$c = new Calif_Carrera ();
		
		$tabla = 'Catalogo_Carreras';
		$sql = new Gatuf_SQL ($this->_con->pfx.$c->views['__catalogo_c__']['tabla'].'.materia=%s', $this->clave);
		$c->views['__catalogo_c__']['where'] = $sql->gen ();
		
		$p['view'] = '__catalogo_c__';
		
		return $c->getList ($p);
	}
	
	public function displaylinkedclave ($extra) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::verMateria', array ($this->clave)).'">'.$this->clave.'</a>';
	}
	
	public function displaytipo ($extra) {
		if ($this->taller && $this->curso) {
			return 'Curso-Taller';
		} else if ($this->curso) {
			return 'Curso';
		} else if ($this->taller) {
			return 'Taller';
		} else if ($this->laboratorio) {
			return 'Laboratorio';
		} else if ($this->seminario) {
			return 'Seminario';
		}
		return 'Desconocido';
	}
	
	public function displaylinkeddepartamento ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::porDepartamento', array ($this->departamento)).'">'.$this->departamento_desc.'</a>';
	}
}
