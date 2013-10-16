<?php

class Calif_Calificacion extends Gatuf_Model {
	/* Manejador de la tabla de calificaciones */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'calificaciones';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'nrc' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Calif_Seccion',
			       'blank' => false,
			),
			'alumno' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Calif_Alumno',
			       'blank' => false,
			),
			'evaluacion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Calif_Evaluacion',
			       'blank' => false,
			),
			'valor' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'is_null' => true,
			),
		);
		
		$this->_a['idx'] = array (
			'calificacion_idx' =>
			array (
			       'col' => 'nrc, alumno, evaluacion',
			       'type' => 'unique',
			),
		);
	}
		
	public function getPromedio ($grupo_eval) {
		$req = sprintf ('SELECT C.alumno, C.nrc, P.grupo, SUM(GREATEST (IFNULL(C.valor,0),0) * P.porcentaje / 100) AS suma FROM %s AS C INNER JOIN %s AS S ON C.nrc = S.nrc INNER JOIN %s AS P ON S.materia = P.materia AND C.evaluacion = P.evaluacion WHERE C.Alumno = %s AND C.nrc = %s AND P.grupo = %s GROUP BY P.grupo', $this->getSqlTable (), Gatuf::factory ('Calif_Seccion')->getSqlTable (), Gatuf::factory('Calif_Porcentaje')->getSqlTable (), Gatuf_DB_IdentityToDb ($this->alumno, $this->_con), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con), Gatuf_DB_IntegerToDb ($grupo_eval, $this->_con));
		
		if (false === ($rs = $this->_con->select ($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		
		return $rs[0]['suma'];
	}
}
