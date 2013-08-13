<?php

class Calif_Maestro extends Calif_User {
	/* Manejador de la tabla Alumnos */
	
	/* Campos */
	public $codigo;
	public $nombre;
	public $apellido;
	public $correo;
	public $maestro_departamento;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Maestros';
		$this->login_tabla = 'Maestros_Login';
		$this->special_tabla = 'Maestros_View';
		$this->default_order = 'apellido ASC, nombre ASC';
	}
    
    function getMaestro ($codigo) {
    	/* Recuperar un maestro */
		$req = sprintf ('SELECT * FROM %s WHERE codigo = %s', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($codigo, $this->_con));
		
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
	
	function displaylinkedcodigo ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::verMaestro', array ($this->codigo)).'">'.$this->codigo.'</a>';
	}
	
	function displaylinkedmaestro_departamento ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('HorarioPorDepartamento', array ($this->codigo, $this->maestro_departamento)).'">Imprimir Horario</a>';
	}
}
