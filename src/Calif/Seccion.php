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
    	
    	$consulta = sprintf ('SELECT * FROM %s WHERE %s', $this->getGruposSqlTable(), $sql->gen());
    	
    	if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return array ();
		}
		
		return $rs;
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
	
	public function displaylinkednrc ($extra) {
		throw new Exception ("Metodo no implementado");
	}
	
	public function displaylinkedmateria ($extra) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::verMateria', array ($this->materia)).'">'.$this->materia.'</a>';
	}
}
