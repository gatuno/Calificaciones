<?php

class Calif_Alumno extends Calif_User {
	/* Manejador de la tabla Alumnos */
	
	/* Campos */
	public $codigo;
	public $carrera;
	public $nombre;
	public $apellido;
	public $correo;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Alumnos';
		$this->login_tabla = 'Alumnos_Login';
	}
    
    function getAlumno ($codigo) {
    	/* Recuperar un alumno */
		$sql = sprintf ('SELECT * FROM %s WHERE Codigo = %s', $this->getSqlTable(), Gatuf_DB_IntegerToDb($codigo, $this->_con));
		
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
		$req = sprintf ('INSERT INTO %s (codigo, carrera, nombre, apellido, correo) VALUES (%s, %s, %s, %s, %s);', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->codigo, $this->_con), Gatuf_DB_IdentityToDb ($this->carrera, $this->_con), Gatuf_DB_IdentityToDb ($this->nombre, $this->_con), Gatuf_DB_IdentityToDb ($this->apellido, $this->_con), Gatuf_DB_IdentityToDb ($this->correo, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function update () {
		/* FIXME: ¿Se debería poder actualizar la carrera? */
		$req = sprintf ('UPDATE %s SET nombre = %s, apellido = %s, correo = %s WHERE codigo = %s;', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->nombre, $this->_con), Gatuf_DB_IdentityToDb ($this->apellido, $this->_con), Gatuf_DB_IdentityToDb ($this->correo, $this->_con), Gatuf_DB_IntegerToDb ($this->codigo, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	public function displaycarrera ($extra) {
		if (!isset ($extra[$this->carrera])) {
			throw new Exception ("Oops: Un alumno tiene registrada una carrera inexistente");
		}
		
		return '<abbr title="'.$extra[$this->carrera].'">'.$this->carrera.'</abbr>';
	}
	
	public function displaylinkedcarrera ($extra) {
		if (!isset ($extra[$this->carrera])) {
			throw new Exception ("Oops: Un alumno tiene registrada una carrera inexistente");
		}
		
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Carrera::verCarrera', array ($this->carrera)).'"><abbr title="'.$extra[$this->carrera].'">'.$this->carrera.'</abbr></a>';
	}
}
