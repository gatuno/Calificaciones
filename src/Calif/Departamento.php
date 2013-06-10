<?php

class Calif_Departamento extends Gatuf_Model {
	/* Manejador de la tabla de departamentos */
	
	/* Campos */
	public $clave;
	public $departamento;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Departamentos';
		
		$this->default_query = array(
                       'select' => '*',
                       'from' => $this->getSqlTable(),
                       'join' => '',
                       'where' => '',
                       'group' => '',
                       'having' => '',
                       'order' => '',
                       'limit' => '',
                       );
	}
	
	function getDepartamento ($id) {
		$req = sprintf ('SELECT * FROM %s WHERE clave = %s', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($id, $this->_con));
		
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
}
