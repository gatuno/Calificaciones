<?php

class Calif_Seccion extends Gatuf_Model {
	/* Manejador de la tabla de secciones */
	
	/* Campos */
	public $nrc;
	public $materia, $materia_desc, $materia_departamento;
	public $seccion;
	public $maestro, $maestro_nombre, $maestro_apellido;
	public $asignacion;
	
	/* La conexión mysql con la base de datos */
	public $_con = null;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Secciones';
		$this->tabla_view = 'Secciones_View';
		$this->tabla_grupos = 'Grupos';
		$this->default_order = 'materia ASC, seccion ASC';
		
		$tabla = 'Grupos';
		
		$this->views['__grupos__'] = array ();
		$this->views['__grupos__']['tabla'] = $tabla;
		$this->views['__grupos__']['join'] = ' LEFT JOIN '.$this->_con->pfx.$tabla.' ON '.$this->getSqlViewTable ().'.nrc='.$this->_con->pfx.$tabla.'.nrc';
		$this->views['__grupos__']['order'] = $this->default_order;
	}
    
	function getNrc ($nrc) {
		/* Recuperar una seccion */
		$req = sprintf ('SELECT * FROM %s WHERE nrc=%s', $this->getSqlViewTable(), Gatuf_DB_IdentityToDb ($nrc, $this->_con));
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		foreach ($rs[0] as $col => $val) {
			$this->$col = $val;
		}
		
		$this->restore ();
		return true;
	}
    
	function getAlumnosList ($p = array ()) {
		$default = array ('view' => null,
		                  'filter' => null,
		                  'order' => null,
		                  'start' => null,
		                  'nb' => null,
		                  'count' => false);
		$p = array_merge ($default, $p);
		
		$a = new Calif_Alumno ();
		
		$sql = new Gatuf_SQL ($this->_con->pfx.$a->views['__grupos__']['tabla'].'.nrc=%s', $this->nrc);
		$a->views['__grupos__']['where'] = $sql->gen ();
		
		$p['view'] = '__grupos__';
		
		return $a->getList ($p);
	}
	
	function addAlumno ($alumno) {
		$req = sprintf ('INSERT INTO %s (nrc, alumno) VALUES (%s, %s)', $this->_con->pfx.$this->tabla_grupos, Gatuf_DB_IdentityToDb ($this->nrc, $this->_con), Gatuf_DB_IdentityToDb ($alumno, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function addAlumnoToRam ($tabla, $alumno) {
		$req = sprintf ('INSERT INTO %s (nrc, alumno) VALUES (%s, %s)', $tabla, Gatuf_DB_IntegerToDb ($this->nrc, $this->_con), Gatuf_DB_IdentityToDb ($alumno, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	/* Eliminar todos los alumnos de este grupo */
	function clearAlumnos () {
		$sql = new Gatuf_SQL ('nrc=%s', $this->nrc);
		$req = sprintf ('DELETE FROM %s WHERE %s', $this->_con->pfx.$this->tabla_grupos, $sql->gen());
		
		$this->_con->execute ($req);
		return true;
	}
	
	function create () {
		$req = sprintf ('INSERT INTO %s (nrc, seccion, materia, maestro) VALUES (%s, %s, %s, %s);', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con), Gatuf_DB_IdentityToDb ($this->seccion, $this->_con), Gatuf_DB_IdentityToDb ($this->materia,$this->_con), Gatuf_DB_IntegerToDb ($this->maestro, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function update () {
		$req = sprintf ('UPDATE %s SET maestro=%s, seccion=%s WHERE nrc=%s', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->maestro, $this->_con), Gatuf_DB_IdentityToDb ($this->seccion, $this->_con), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function updateAsignacion () {
		$req = sprintf ('UPDATE %s SET asignacion = %s WHERE nrc=%s AND asignacion IS NULL', $this->getSqlTable (), Gatuf_DB_IdentityToDb ($this->asignacion, $this->_con), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con));
		
		$this->_con->execute($req);
		
		$req = sprintf ('SELECT asignacion FROM %s WHERE nrc = %s', $this->getSqlTable (), $this->nrc);
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if ($rs[0]['asignacion'] != $this->asignacion) {
			return false;
		}
		return true;
	}
	
	function liberarAsignacion () {
		$req = sprintf ('UPDATE %s SET asignacion = NULL WHERE nrc=%s', $this->getSqlTable (), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function maxNrc () {
		$req = sprintf ('SELECT MAX(nrc) AS max_nrc FROM %s', $this->getSqlTable ());
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		return $rs[0]['max_nrc'];
	}
	
	function maxSeccion ($materia) {
		$req = sprintf ('SELECT MAX(seccion) AS max_seccion FROM %s WHERE materia=%s', $this->getSqlTable (), Gatuf_DB_IdentityToDb ($materia, $this->_con));
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		return $rs[0]['max_seccion'];
	}
	
	function delete () {
		$req = sprintf ('DELETE FROM %s WHERE nrc=%s', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con));
		
		$this->_con->execute ($req);
		
		return true;
	}
	
	function cambiaNrc ($new_nrc) {
		$req = sprintf ('UPDATE %s SET nrc = %s WHERE nrc = %s', $this->getSqlTable (), $new_nrc, $this->nrc);
		
		$this->_con->execute($req);
		
		$this->nrc = $new_nrc;
		
		return true;
	}
	
	public function displaylinkedseccion ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($this->nrc)).'">'.$this->seccion.'</a>';
	}
	
	public function displaylinkednrc ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($this->nrc)).'">'.$this->nrc.'</a>';
	}
	
	public function displaylinkedmateria ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::verMateria', array ($this->materia)).'">'.$this->materia.' - '.$this->materia_desc.'</a>';
	}
	
	public function displaylinkedmaestro ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::verMaestro', array ($this->maestro)).'">'.$this->maestro_apellido.' '.$this->maestro_nombre.' ('.$this->maestro.')</a>';
	}
	
	public function displaylinkedmaestro_apellido ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::verMaestro', array ($this->maestro)).'">'.$this->maestro_apellido.' '.$this->maestro_nombre.' ('.$this->maestro.')</a>';
	}
}
