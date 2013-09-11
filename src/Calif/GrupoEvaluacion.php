<?php

class Calif_GrupoEvaluacion extends Gatuf_Model {
	/* Controlador de los grupos de evaluacion */
	
	/* Campos */
	public $id;
	public $descripcion;
	
	public function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Grupos_Evaluaciones';
	}
	
	public function getGrupoEval ($id) {
		$req = sprintf ('SELECT * FROM %s WHERE id = %s', $this->getSqlTable (), $id);
		
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
	/* TODO: Hacer funcion create () */
}
