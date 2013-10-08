<?php

class Calif_Horario extends Gatuf_Model {
	/* Manejador de la tabla de horarios */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'horarios';
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
			       'blank' => false,
			       'model' => 'Calif_Seccion',
			),
			'inicio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Time',
			       'blank' => false,
			),
			'fin' =>
			array (
			       'type' => 'Gatuf_DB_Field_Time',
			       'blank' => false,
			),
			'salon' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Calif_Salon',
			),
		);
		foreach (array ('l', 'm', 'i', 'j', 'v', 's', 'd') as $dia) {
			$this->_a['cols'][$dia] = array (
				'type' => 'Gatuf_DB_Field_Boolean',
				'blank' => false,
				'default' => false,
			);
		};
		$this->_a['views'] = array (
			'paginador' => array (
				'select' => 'horarios_view.*',
				'from' => 'horarios_view',
				'props' => array ('salon_aula', 'salon_edificio', 'seccion_maestro', 'seccion_asignacion', 'seccion_asignacion_color'),
			),
		);
	}
	
	function displayDias () {
		$cadena = '';
		foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
			if ($this->$dia) {
				$cadena .= mb_strtoupper ($dia);
			} else {
				$cadena .= '.';
			}
		}
		return $cadena;
	}
	
	public static function chocan ($a, $b) {
		$coincide = false;
		foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
			if ($a->$dia && $b->$dia) {
				$coincide = true;
				break;
			}
		}
		
		if (!$coincide) return false;
		
		$a_inicio = strtotime ($a->inicio);
		$a_fin = strtotime ($a->fin);
		$b_inicio = strtotime ($b->inicio);
		$b_fin = strtotime ($b->fin);
		
		if (($a_inicio >= $b_inicio && $a_fin < $b_fin) ||
			($a_fin > $b_inicio && $a_fin <= $b_fin) ||
			($a_inicio <= $b_inicio && $a_fin >= $b_fin)) {
			return true;
		}
		
		return false;
	}
}
