<?php

class Calif_Carrera extends Gatuf_Model {
	/* Manejador de la tabla de carreras */
	
	/* Campos */
	public $clave;
	public $descripcion;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Carreras';
	}
	
	function getCarrera ($clave) {
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
	
	public function getMateriasList ($p = array ()) {
		$default = array('view' => null,
		                 'filter' => null,
		                 'order' => null,
		                 'start' => null,
		                 'nb' => null,
		                 'count' => false);
		$p = array_merge($default, $p);
		
		$m = new Calif_Materia ();
		$tabla = 'Catalogo_Carreras';
		
		$m->views['__manytomany__'] = array ();
		$m->views['__manytomany__']['join'] = ' LEFT JOIN '.$this->_con->pfx.$tabla.' ON '.$m->getSqlViewTable().'.clave='.$this->_con->pfx.$tabla.'.materia';
		$sql = new Gatuf_SQL ($this->_con->pfx.$tabla.'.carrera=%s', $this->clave);
		
		$m->views['__manytomany__']['where'] = $sql->gen ();
		
		$p['view'] = '__manytomany__';
		
		return $m->getList ($p);
	}
}
