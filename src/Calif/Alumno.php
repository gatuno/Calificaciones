<?php

class Calif_Alumno extends Calif_User {
	/* Manejador de la tabla Alumnos */
	
	/* Campos */
	public $codigo;
	public $carrera;
	public $carrera_desc;
	public $nombre;
	public $apellido;
	public $correo;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Alumnos';
		$this->tabla_view = 'Alumnos_View';
		$this->login_tabla = 'Alumnos_Login';
		$this->default_order = 'apellido ASC, nombre ASC';
		
		$tabla = 'Grupos';
		
		$this->views['__grupos__'] = array ();
		$this->views['__grupos__']['tabla'] = $tabla;
		$this->views['__grupos__']['join'] = ' LEFT JOIN '.$this->_con->pfx.$tabla.' ON '.$this->getSqlViewTable().'.codigo='.$this->_con->pfx.$tabla.'.alumno';
		$this->views['__grupos__']['order'] = $this->default_order;
	}
    
    function getAlumno ($codigo) {
    	/* Recuperar un alumno */
		$req = sprintf ('SELECT * FROM %s WHERE Codigo = %s', $this->getSqlTable(), Gatuf_DB_IdentityToDb($codigo, $this->_con));
		
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
		$req = sprintf ('INSERT INTO %s (codigo, carrera, nombre, apellido, correo) VALUES (%s, %s, %s, %s, %s);', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->codigo, $this->_con), Gatuf_DB_IdentityToDb ($this->carrera, $this->_con), Gatuf_DB_IdentityToDb ($this->nombre, $this->_con), Gatuf_DB_IdentityToDb ($this->apellido, $this->_con), Gatuf_DB_IdentityToDb ($this->correo, $this->_con));
		
		$this->_con->execute($req);
		
		$this->createSession ();
		
		return true;
	}
	
	function update () {
		/* FIXME: ¿Se debería poder actualizar la carrera? */
		$req = sprintf ('UPDATE %s SET nombre = %s, apellido = %s, correo = %s WHERE codigo = %s;', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->nombre, $this->_con), Gatuf_DB_IdentityToDb ($this->apellido, $this->_con), Gatuf_DB_IdentityToDb ($this->correo, $this->_con), Gatuf_DB_IdentityToDb ($this->codigo, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	public function getSeccionesList ($p = array ()) {
		$default = array ('view' => null,
		                  'filter' => null,
		                  'order' => null,
		                  'start' => null,
		                  'nb' => null,
		                  'count' => false);
		$p = array_merge ($default, $p);
		
		$s = new Calif_Seccion ();
		
		$sql = new Gatuf_SQL ($this->_con->pfx.$s->views['__grupos__']['tabla'].'.alumno=%s', $this->codigo);
		$s->views['__grupos__']['where'] = $sql->gen ();
		
		$p['view'] = '__grupos__';
		
		return $s->getList ($p);
	}
	
	public function displaycarrera ($extra=null) {
		return '<abbr title="'.$this->carrera_desc.'">'.$this->carrera.'</abbr>';
	}
	
	public function displaylinkedcarrera ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Carrera::verCarrera', array ($this->carrera)).'"><abbr title="'.$this->carrera_desc.'">'.$this->carrera.'</abbr></a>';
	}
	
	public function displaylinkedcodigo ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Alumno::verAlumno', array ($this->codigo)).'"><abbr title="'.$this->codigo.'">'.$this->codigo.'</abbr></a>';
	}
}
