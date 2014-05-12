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
		
		Gatuf::loadFunction ('Calif_Calendario_getDefault');
		$this->_a['calpfx'] = Calif_Calendario_getDefault ();
	}
		
	public static function getPromedio ($alumno, $nrc, $grupo_eval) {
		static $calificacion_tabla = null;
		static $seccion_tabla = null;
		static $porcentaje_tabla = null;
		static $db = null;
		
		if ($db === null) {
			$db = Gatuf::db ();
			$calificacion_tabla = Gatuf::factory ('Calif_Calificacion')->getSqlTable ();
			$seccion_tabla = Gatuf::factory ('Calif_Seccion')->getSqlTable ();
			$porcentaje_tabla = Gatuf::factory ('Calif_Porcentaje')->getSqlTable ();
		}
		
		$req = sprintf ('SELECT C.alumno, C.nrc, P.grupo, SUM(GREATEST (COALESCE(C.valor,0),0) * P.porcentaje / 100) AS suma FROM %s AS C INNER JOIN %s AS S ON C.nrc = S.nrc INNER JOIN %s AS P ON S.materia = P.materia AND C.evaluacion = P.evaluacion WHERE C.Alumno = %s AND C.nrc = %s AND P.grupo = %s GROUP BY P.grupo', $calificacion_tabla, $seccion_tabla, $porcentaje_tabla, Gatuf_DB_IdentityToDb ($alumno, $db), Gatuf_DB_IntegerToDb ($nrc, $db), Gatuf_DB_IntegerToDb ($grupo_eval, $db));
		
		if (false === ($rs = $db->select ($req))) {
			throw new Exception($db->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		
		return $rs[0]['suma'];
	}
}
