<?php

class Calif_Departamento extends Gatuf_Model {
	/* Manejador de la tabla de departamentos */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'departamentos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'clave';
		
		$this->_a['cols'] = array (
			'clave' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 100,
			),
		);
	}

	function postSave ($create = false) {
		$perm = new Gatuf_Permission();
		$perm->name = 'Jefe de '.$this.clave;
		$perm->code_name = 'jefe.'.$this.clave;
		$perm->descripcion = "Permite alterar y crear materias del ".$this->descripcion;
		$perm->application = "SIIAU";
		$perm->save();
	}
}
