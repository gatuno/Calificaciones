<?php

Gatuf::loadFunction ('Gatuf_DB_getConnection');

class Calif_Carrera {
	/* Manejador de la tabla de carreras */
	
	/* Campos */
	public $clave;
	public $descripcion;
	
	/* La tabla de donde recoger los datos */
	public $tabla;
	
	/* La conexión mysql con la base de datos */
	public $_con = null;
	
	function __construct () {
		$this->_getConnection();
		$prefix = Gatuf::config ('db_table_prefix', '');
		
		$this->tabla = $prefix.'Carreras';
	}
	
	function _getConnection() {
		static $con = null;
		if ($this->_con !== null) {
			return $this->_con;
		}
		if ($con !== null) {
			$this->_con = $con;
			return $this->_con;
		}
		$this->_con = &Gatuf::db($this);
		$con = $this->_con;
		return $this->_con;
    }
    
	function getCarrera ($clave) {
		/* Recuperar una carrera */
		$sql = sprintf ("SELECT * FROM %s WHERE Clave = '%s'", $this->tabla, $clave);
		
		$result = mysql_query ($sql, $this->_con);
		
		if (mysql_num_rows ($result) == 0) {
			return null;
		} else {
			$object = mysql_fetch_object ($result);
			$this->clave = $object->Clave;
			$this->descripcion = $object->Descripcion;
			
			mysql_free_result ($result);
		}
	}
	
	function getList () {
		$todas_las_carreras = array ();
		
		$sql = sprintf ("SELECT * FROM %s", $this->tabla);
		
		$result = mysql_query ($sql, $this->_con);
		
		while (($object = mysql_fetch_object ($result))) {
			$car_temp = new Calif_Carrera ();
			$car_temp->clave = $object->Clave;
			$car_temp->descripcion = $object->Descripcion;
			$todas_las_carreras[] = $car_temp;
		}
		
		mysql_free_result ($result);
		
		return $todas_las_carreras;
	}
}
