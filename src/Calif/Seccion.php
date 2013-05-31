<?php

class Calif_Seccion extends Gatuf_Model {
	/* Manejador de la tabla de secciones */
	
	/* Campos */
	public $nrc;
	public $materia;
	public $seccion;
	public $maestro;
	
	public $tabla_grupos;
	
	/* La conexión mysql con la base de datos */
	public $_con = null;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Secciones';
		$this->tabla_grupos = 'Grupos';
	}
	
	function getGruposSqlTable () {
		return $this->_con->pfx.$this->tabla_grupos;
	}
    
	function getNrc ($nrc) {
		/* Recuperar una seccion */
		$req = sprintf ('SELECT * FROM %s WHERE nrc=%s', $this->tabla, Gatuf_DB_IdentityToDb ($nrc, $this->_con));
		
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
    
    function getAlumnos () {
    	$sql = new Gatuf_SQL ('nrc=%s', $this->nrc);
    	
    	$req = sprintf ('SELECT * FROM %s WHERE %s', $this->getGruposSqlTable(), $sql->gen());
    	
    	if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return array ();
		}
		
		return $rs;
    }
	
	function addAlumno ($alumno) {
		$req = sprintf ('INSERT INTO %s (nrc, alumno) VALUES (%s, %s)', $this->getGruposSqlTable (), Gatuf_DB_IdentityToDb ($this->nrc, $this->_con), Gatuf_DB_IdentityToDb ($alumno, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function addAlumnoToRam ($tabla, $alumno) {
		$req = sprintf ('INSERT INTO %s (nrc, alumno) VALUES (%s, %s)', $tabla, Gatuf_DB_IdentityToDb ($this->nrc, $this->_con), Gatuf_DB_IdentityToDb ($alumno, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	/* Eliminar todos los alumnos de este grupo */
	function clearAlumnos () {
		$sql = new Gatuf_SQL ('nrc=%s', $this->nrc);
		$req = sprintf ('DELETE FROM %s WHERE %s', $this->getGruposSqlTable (), $sql->gen());
		
		$this->_con->execute ($req);
		return true;
	}
	
	function create () {
		$req = sprintf ('INSERT INTO %s (nrc, seccion, materia, maestro) VALUES (%s, %s, %s, %s);', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con), Gatuf_DB_IdentityToDb ($this->seccion, $this->_con), Gatuf_DB_IdentityToDb ($this->materia,$this->_con), Gatuf_DB_IntegerToDb ($this->maestro, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function update () {
		$req = sprintf ('UPDATE %s SET maestro=%s WHERE nrc=%s', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->maestro, $this->_con), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function delete () {
		$req = sprintf ('DELETE FROM %s WHERE nrc=%s', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con));
		
		$this->_con->execute ($req);
		
		return true;
	}
	
	public function displaylinkedseccion ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($this->nrc)).'">'.$this->seccion.'</a>';
	}
	
	public function displaylinkednrc ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($this->nrc)).'">'.$this->nrc.'</a>';
	}
	
	public function displaylinkedmateria ($extra=null) {
		$m = new Calif_Materia ();
		$m->getMateria ($this->materia);
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::verMateria', array ($this->materia)).'">'.$m->clave.' - '.$m->descripcion.'</a>';
	}
	
	public function displaylinkedmaestro ($extra=null) {
		$m = new Calif_Maestro ();
		$m->getMaestro ($this->maestro);
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::verMaestro', array ($this->maestro)).'">'.$m->apellido.' '.$m->nombre.' ('.$m->codigo.')</a>';
	}
}
