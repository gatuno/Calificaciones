<?php

class Calif_Carrera extends Gatuf_Model {
	/* Manejador de la tabla de carreras */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'carreras';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'clave';
		
		$this->_a['cols'] = array (
			'clave' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 5,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 120,
			),
			'color' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 0,
			),
		);
		$this->default_order = 'clave ASC, descripcion ASC';
	}
	
	function postSave ($create=false) {
		if ($create) {
			$permiso = new Gatuf_Permission ();
			$permiso->name = 'Coordinador de '.$this->clave;
			$permiso->code_name = 'coordinador.'.$this->clave;
			$permiso->description = 'Permite alterar y crear secciones de la carrera "'.$this->descripcion.'"';
			$permiso->application = 'SIIAU';
		
			$permiso->create ();
		}
	}
	
	function preDelete () {
		$sql = Gatuf_SQL ('code_name=%s AND application=%s', array ('coordinador.'.$this->clave, 'SIIAU'));
		$permiso = Gatuf::factory ('Gatuf_Permission')->getList (array ('filter' => $sql->gen ()));
		
		foreach ($permiso as $p) {
			$p->delete ();
		}
	}
}
