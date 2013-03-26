<?php

Gatuf::loadFunction ('Gatuf_DB_getConnection');

class Calif_Carrera {
	/* Manejador de la tabla de carreras */
	
	/* Campos */
	public $clave;
	public $descripcion;
	
	/* La tabla de donde recoger los datos */
	public $tabla;
	public $conid;
	
	function __construct () {
		$prefix = Gatuf::config ('db_table_prefix', '');
		
		if ($prefix !== '') {
			$this->tabla = $prefix.'_Carreras';
		} else {
			$this->tabla = 'Carreras';
		}
		$this->conid = Gatuf_DB_getConnection ();
	}
	
	function getCarrera ($clave) {
		/* Recuperar una carrera */
		$sql = sprintf ("SELECT * FROM %s WHERE Clave = '%s'", $this->tabla, $clave);
		
		$result = mysql_query ($sql, $this->conid);
		
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
		
		$result = mysql_query ($sql, $this->conid);
		
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
