<?php

class Calif_Maestro extends Gatuf_Model {
	/* Manejador de la tabla Maestros */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'maestros';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'codigo';
		
		$this->_a['cols'] = array (
			'codigo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
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
			/* TODO: Falta grado y sexo */
		);
		
		$this->default_order = 'apellido ASC, nombre ASC';
		
		$this->_a['views'] = array (
			'maestros_departamentos' => array (
				'select' => $this->_con->pfx.'maestros_departamentos.*',
				'from' => $this->_con->pfx.'maestros_departamentos',
				'props' => array ('departamento'),
			),
		);
	}
	
	function getUser () {
		$sql = new Gatuf_SQL ('login=%s', $this->codigo);
		$this->user = Gatuf::factory ('Calif_User')->getOne (array ('filter' => $sql->gen ()));
	}
	
	function displaylinkedcodigo ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::verMaestro', array ($this->codigo)).'">'.$this->codigo.'</a>';
	}
	
	function displaylinkeddepartamento ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('HorarioPorDepartamento', array ($this->codigo, $this->departamento)).'">Imprimir Horario</a>';
	}
}
