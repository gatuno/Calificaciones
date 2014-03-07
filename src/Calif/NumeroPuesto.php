<?php

class Calif_NumeroPuesto extends Gatuf_Model {
	/* Manejador de la tabla de numero de puesto */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'numero_puestos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'numero';
		
		$this->default_order = 'tipo ASC';
		
		$this->_a['cols'] = array (
			'numero' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'nrc' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Calif_Seccion',
			),
			'carga' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			       'default' => 'a', /* Asignatura, Tiempo completo, Honorifico */
			),
			'tipo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			       'default' => 't',
			),
			'horas' =>
			array (
			       'type' => 'Gatuf_DB_Field_Float',
			       'blank' => false,
			       'decimal_places' => 2,
			       'max_digits' => 5,
			),
		);
		
		$this->_a['idx'] = array (
			'tipo_idx' =>
			array (
			       'col' => 'tipo',
			       'type' => 'index',
			),
			'carga_idx' =>
			array (
			       'col' => 'carga',
			       'type' => 'index',
			),
		);
	}
	
	function maxPuesto () {
		$req = sprintf ('SELECT MAX(numero) AS max_numero FROM %s', $this->getSqlTable ());
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		return (int) $rs[0]['max_numero'];
	}
	
	static public function maestro_cambiado ($signal, &$params) {
		/* En caso de que se actualice, asegurarse que si profesor de asignatura, los puestos también */
		$nrc = $params['nrc'];
		
		$maestro = new Calif_Maestro ($nrc->maestro);
		
		$puestos = $nrc->get_calif_numeropuesto_list ();
		
		foreach ($puestos as $puesto) {
			/* Probar si cabe en su tiempo completo */
			$tiempo_c = $maestro->maxTiempoCompleto () - $maestro->getCarga('t')->horas;
			$tiempo_a = $maestro->maxAsignatura () - $maestro->getCarga ('a')->horas;
			
			if ($tiempo_c >= $puesto->horas) {
				$puesto->carga = 't';
			} else if ($tiempo_a >= $puesto->horas) {
				$puesto->carga = 'a';
			} else {
				$puesto->carga = 'h';
			}
			$puesto->update ();
		}
	}

	static public function suplente_creado ($signal, &$params) {
		$nrc = $params['nrc'];
		
		$materia = new Calif_Materia ($nrc->materia);
		$suplente = new Calif_Maestro ($nrc->suplente);

		if ($materia->teoria > 0) {
			/* Crear un número de puesto para las horas de suplente teoria */
			$puesto = new Calif_NumeroPuesto ();
			$numero = $puesto->maxPuesto ();
			
			if ($numero < 99000) $numero = 99000;
			$puesto->numero = $numero + 1;
			$puesto->nrc = $nrc;
			$puesto->tipo = 'u';
			$puesto->horas = $materia->teoria;
			
			$tiempo_c = $suplente->maxTiempoCompleto () - $suplente->getCarga('t')->horas;
			$tiempo_a = $suplente->maxAsignatura () - $suplente->getCarga ('a')->horas;
			
			if ($tiempo_c >= $puesto->horas) {
				$puesto->carga = 't';
			} else if ($tiempo_a >= $puesto->horas) {
				$puesto->carga = 'a';
			} else {
				$puesto->carga = 'h';
			}
			
			$puesto->create ();
		}

		if ($materia->practica > 0) {
			/* Crear un número de puesto para las horas de suplente practica */
			$puesto = new Calif_NumeroPuesto ();
			$numero = $puesto->maxPuesto ();
			
			if ($numero < 99000) $numero = 99000;
			$puesto->numero = $numero + 1;
			$puesto->nrc = $nrc;
			$puesto->tipo = 'q';
			$puesto->horas = $materia->practica;
			
			$tiempo_c = $suplente->maxTiempoCompleto () - $suplente->getCarga('t')->horas;
			$tiempo_a = $suplente->maxAsignatura () - $suplente->getCarga ('a')->horas;
			
			if ($tiempo_c >= $puesto->horas) {
				$puesto->carga = 't';
			} else if ($tiempo_a >= $puesto->horas) {
				$puesto->carga = 'a';
			} else {
				$puesto->carga = 'h';
			}
			$puesto->create ();
		}
	}
	
	static public function suplente_eliminado ($signal, &$params) {
	
	}

	static public function nrc_creado ($signal, &$params) {
		/* Si el NRC se está creando, crear sus números de puesto */
		$nrc = $params['nrc'];
		
		$materia = new Calif_Materia ($nrc->materia);
		$maestro = new Calif_Maestro ($nrc->maestro);
		
		if ($materia->teoria > 0) {
			/* Crear un número de puesto para las horas de teoria */
			$puesto = new Calif_NumeroPuesto ();
			$numero = $puesto->maxPuesto ();
			
			if ($numero < 99000) $numero = 99000;
			$puesto->numero = $numero + 1;
			$puesto->nrc = $nrc;
			$puesto->tipo = 't';
			$puesto->horas = $materia->teoria;
			
			$tiempo_c = $maestro->maxTiempoCompleto () - $maestro->getCarga('t')->horas;
			$tiempo_a = $maestro->maxAsignatura () - $maestro->getCarga ('a')->horas;
			
			if ($tiempo_c >= $puesto->horas) {
				$puesto->carga = 't';
			} else if ($tiempo_a >= $puesto->horas) {
				$puesto->carga = 'a';
			} else {
				$puesto->carga = 'h';
			}
			
			$puesto->create ();
		}
		
		if ($materia->practica > 0) {
			/* Crear un número de puesto para las horas de teoria */
			$puesto = new Calif_NumeroPuesto ();
			$numero = $puesto->maxPuesto ();
			
			if ($numero < 99000) $numero = 99000;
			$puesto->numero = $numero + 1;
			$puesto->nrc = $nrc;
			$puesto->tipo = 'p';
			$puesto->horas = $materia->practica;
			
			$tiempo_c = $maestro->maxTiempoCompleto () - $maestro->getCarga('t')->horas;
			$tiempo_a = $maestro->maxAsignatura () - $maestro->getCarga ('a')->horas;
			
			if ($tiempo_c >= $puesto->horas) {
				$puesto->carga = 't';
			} else if ($tiempo_a >= $puesto->horas) {
				$puesto->carga = 'a';
			} else {
				$puesto->carga = 'h';
			}
			
			$puesto->create ();
		}
	}
	
	public static function materia_horas_actualizado ($signal, &$params) {
		$materia = $params['materia'];
		
		if ($params['tipo'] == 'teoria') {
			$tipo = 'teoria';
			$tipo_abr = 't'; 
		} else {
			$tipo = 'practica';
			$tipo_abr = 'p'; 
		}
		
		$nrcs = $materia->get_calif_seccion_list ();
		
		$sql = new Gatuf_SQL ('tipo=%s', $tipo_abr);
		$where = $sql->gen ();
		
		$puesto_model = new Calif_NumeroPuesto ();
		
		foreach ($nrcs as $nrc) {
			$puesto = $nrc->get_calif_numeropuesto_list (array ('filter' => $where));
			
			if ($puesto->count () == 0 && $materia->$tipo > 0) {
				/* Si no tenía horas y ahora sí, crear el puesto */
				$numero = $puesto_model->maxPuesto ();
					
				if ($numero < 99000) $numero = 99000;
				$puesto_model->numero = $numero + 1;
				$puesto_model->nrc = $nrc;
				$puesto_model->tipo = $tipo_abr;
				$puesto_model->horas = $materia->$tipo;
				
				$maestro = new Calif_Maestro ($nrc->maestro);
				
				$tiempo_c = $maestro->maxTiempoCompleto () - $maestro->getCarga('t')->horas;
				$tiempo_a = $maestro->maxAsignatura () - $maestro->getCarga ('a')->horas;
				
				if ($tiempo_c >= $puesto_model->horas) {
					$puesto->carga = 't';
				} else if ($tiempo_a >= $puesto_model->horas) {
					$puesto->carga = 'a';
				} else {
					$puesto->carga = 'h';
				}
				
				$puesto_model->create ();
			} else if ($puesto->count () != 0) {
				if ($materia->$tipo == 0) {
					/* Hay un número de puesto, y ya no tiene horas. Eliminarlo */
					$puesto[0]->delete ();
				} else {
					/* Peor aún, son diferentes */
					$puesto[0]->horas = $materia->$tipo;
					$puesto[0]->update ();
				}
			}
		}
	}
}
