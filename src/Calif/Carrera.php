<?php
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
			$this->tabla;
		}
		$this->conid = Gatuf_DB_getConnection ();
	}
	
	static function getCarrera ($clave) {
		/* Recuperar una carrera */
		$carrera = new Calif_Carrera ();
		
		$sql = sprintf ("SELECT * FROM %s WHERE Clave = '%s'", $carrera->tabla, $clave);
		
		$result = mysql_query ($sql, $carrera->conid);
		
		if (mysql_num_rows ($result) == 0) {
			return null;
		} else {
			$object = mysql_fetch_object ($result);
			$carrera->clave = $object->Clave;
			$carrera->descripcion = $object->Descripcion;
			
			mysql_free_result ($result);
		}
	}
	
	static function getList () {
		$carrera_vacia = new Calif_Carrera ();
		$todas_las_carreras = array ();
		
		$sql = sprintf ("SELECT * FROM %s", $carrera_vacia->tabla);
		
		$result = mysql_fetch_result ($sql, $carrera_vacia->conid);
		
		while (($object = mysql_fetch_object ($result))) {
			$car_temp = new Calif_Carrera ();
			$car_temp->Clave = $object->Clave;
			$car_temp->Descripcion = $object->Descripcion;
			$todas_las_carreras[] = $car_temp;
		}
		
		mysql_free_result ($result);
		
		return $todas_las_carreras;
	}
}
