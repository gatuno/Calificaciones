<?php

class Calif_Maestro extends Calif_User {
	/* Manejador de la tabla Alumnos */
	
	/* Campos */
	public $codigo;
	public $nombre;
	public $apellido;
	public $correo;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Maestros';
		$this->login_tabla = 'Maestros_Login';
	}
    
    function getMaestro ($codigo) {
    	/* Recuperar un maestro */
		$req = sprintf ('SELECT * FROM %s WHERE codigo = %s', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($codigo, $this->_con));
		
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
		$req = sprintf ('INSERT INTO %s (codigo, nombre, apellido, correo) VALUES (%s, %s, %s, %s);', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->codigo, $this->_con), Gatuf_DB_IdentityToDb ($this->nombre, $this->_con), Gatuf_DB_IdentityToDb ($this->apellido, $this->_con), Gatuf_DB_IdentityToDb ($this->correo, $this->_con));
		
		$this->_con->execute($req);
		
		$this->createSession ();
		
		return true;
	}
	
	function update () {
		$this->preSave ();
		$req = sprintf ('UPDATE %s SET nombre = %s, apellido = %s, correo = %s WHERE codigo = %s;', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->nombre, $this->_con), Gatuf_DB_IdentityToDb ($this->apellido, $this->_con), Gatuf_DB_IdentityToDb ($this->correo, $this->_con), Gatuf_DB_IntegerToDb ($this->codigo, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
}
