<?php

class Calif_Alumno extends Gatuf_Model {
	/* Manejador de la tabla Alumnos */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'alumnos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'codigo';
		
		$this->_a['cols'] = array (
			'codigo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 9,
			),
			'carrera' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Calif_Carrera',
			       'blank' => false,
			),
			'nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 50,
			),
			'apellido' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 100,
			),
			'correo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Email',
			       'blank' => false,
			),
			'grupos' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'blank' => false,
			       'model' => 'Calif_Seccion',
			       'relate_name' => 'grupos',
			),
		);
		
		$this->default_order = 'apellido ASC, nombre ASC';
	}
	
	public function displaycarrera ($extra=null) {
		return '<abbr title="'.$this->carrera_desc.'">'.$this->carrera.'</abbr>';
	}
	
	public function displaylinkedcarrera ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Carrera::verCarrera', array ($this->carrera)).'"><abbr title="'.$this->carrera_desc.'">'.$this->carrera.'</abbr></a>';
	}
}
