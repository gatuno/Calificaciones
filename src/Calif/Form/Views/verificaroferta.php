<?php

class Calif_Form_Views_verificaroferta extends Gatuf_Form {
	public function initFields($extra=array()) {
		Gatuf::loadFunction ('Calif_Utils_dontmove');
		
		$this->fields['siiau'] = new Gatuf_Form_Field_File (
			array('label' => 'Seleccionar archivo',
				'help_text' => 'La oferta de siiau del departamento a verificar',
				'move_function_params' => array(),
				'max_size' => 10485760,
				'move_function' => 'Calif_Utils_dontmove'
		));
		
		$departamentos = Gatuf::factory('Calif_Departamento')->getList (array ('order' => array ('departamento ASC')));
		
		$choices = array ();
		foreach ($departamentos as $depa) {
			$choices[$depa->departamento] = $depa->clave;
		}
		$this->fields['departamento'] = new Gatuf_Form_Field_Varchar (
			array ('required' => true,
				'label' => 'Departamento',
				'initial' => '',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'help_text' => 'El departamento a verificar',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	function save ($commit=true) {
		Gatuf::loadFunction ('Calif_Utils_detectarColumnas');
		Gatuf::loadFunction ('Calif_Utils_agregar_seccion');
		
		$ruta = $this->data['siiau']['tmp_name'];
		
		if (($archivo = fopen ($ruta, "r")) === false) {
			throw new Exception ('Falló al abrir el archivo '.$ruta);
		}
		
		/* Detectar cabeceras */
		$linea = fgetcsv ($archivo, 600, ',', '"');
		
		if ($linea === false || is_null ($linea)) {
			throw new Exception ('No hay cabecera, o es una linea vacia');
		}
		
		$cabecera = Calif_Utils_detectarColumnas ($linea);
		
		/* Verificar que existan los campos necesarios */
		if (!isset ($cabecera['secc']) || !isset ($cabecera['nrc'])) {
			throw new Exception ('El archivo no contiene columna de NRC o Sección');
		}
		
		if (!isset ($cabecera['clave']) || !isset ($cabecera['materia'])) {
			throw new Exception ('El archivo no contiene columna de clave de materia');
		}
		
		if (!isset ($cabecera['profesor'])) {
			throw new Exception ('El archivo no contiene columna de profesor');
		}
		
		if (!isset ($cabecera['edif']) || !isset ($cabecera['aula'])) {
			throw new Exception ('El archivo no contiene edificios o aulas');
		}
		
		if (!isset ($cabecera['ini']) || !isset ($cabecera['fin']) ||
			    !isset ($cabecera['l']) || !isset ($cabecera['m']) ||
			    !isset ($cabecera['i']) || !isset ($cabecera['j']) ||
			    !isset ($cabecera['v']) || !isset ($cabecera['s'])) {
			throw new Exception ('El archivo no contiene información sobre los horarios (hora de inicio, hora de fin, lunes, martes, ...)');
		}
		
		$secciones = array ();
		$horas_por_seccion = array ();
		$observaciones = array ();
		$maestros = array ();
		/* Primera pasada, llenar todos los nrc diferentes */
		while (($linea = fgetcsv ($archivo, 600, ",", "\"")) !== FALSE) {
			if (is_null ($linea[0])) continue; /* Linea vacia */
			$codigo_del_maestro = Calif_Utils_agregar_maestro ($maestros, $linea[$cabecera['profesor']], 0);
			
			$nrc = $linea[$cabecera['nrc']];
			Calif_Utils_agregar_seccion ($secciones, $nrc, $linea[$cabecera['clave']], $linea[$cabecera['secc']], $codigo_del_maestro);
			
			$horas_por_seccion [$nrc] = array ();
			$observaciones [$nrc] = array ('salon' => true, 'edificio' => true, 'hora' => true, 'dias' => true);
			if ($codigo_del_maestro == 0) $observaciones [$nrc]['maestro'] = 0;
			else if ($codigo_del_maestro == '1111111') $observaciones [$nrc]['maestro'] = 1; /* Staff */
			else $observaciones [$nrc]['maestro'] = 2; /* Un maestro no vacio diferente de Staff */
		}
		
		/* Rebobinar el archivo y descartar la fila de las cabeceras */
		rewind ($archivo);
		$linea = fgetcsv ($archivo, 600, ',', '"');
		
		while (($linea = fgetcsv ($archivo, 600, ",", "\"")) !== FALSE) {
			if (is_null ($linea[0])) continue; /* Linea vacia */
			$nrc = $linea[$cabecera['nrc']];
			
			if ($linea[$cabecera['edif']] == '') {
				$observaciones[$nrc]['edificio'] = false;
			}
			
			if ($linea[$cabecera['aula']] == '') {
				$observaciones[$nrc]['salon'] = false;
			}
			
			if ($linea[$cabecera['ini']] == '' || $linea[$cabecera['fin']] == '') {
				$observaciones[$nrc]['hora'] = false;
			}
			
			$activo = false;
			foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
				if ($linea[$cabecera[$dia]] != '') $activo = true;
			}
			
			if ($activo === false) {
				$observaciones[$nrc]['dias'] = false;
			}
			$horario = array ('nrc' => $nrc,
			                  'inicio' => $linea[$cabecera['ini']],
			                  'fin' => $linea[$cabecera['fin']],
			                  'aula' => $linea[$cabecera['aula']],
			                  'edificio' => $linea[$cabecera['edif']],
			                  'lunes' => (boolean) $linea[$cabecera['l']],
			                  'martes' => (boolean) $linea[$cabecera['m']],
			                  'miercoles' => (boolean) $linea[$cabecera['i']],
			                  'jueves' => (boolean) $linea[$cabecera['j']],
			                  'viernes' => (boolean) $linea[$cabecera['v']],
			                  'sabado' => (boolean) $linea[$cabecera['s']]);
			$horas_por_seccion[$nrc][] = $horario;
		}
		
		fclose ($archivo);
		
		/* Recuperar la lista de secciones que nos interesan */
		$materia = new Calif_Materia ();
		
		$cad = sprintf ('%s.departamento=%%s', $materia->getSqlTable ());
		$sql = new Gatuf_SQL ($cad, $this->cleaned_data['departamento']);
		$secciones_solicitadas = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen ()));
		
		$observaciones_solicitadas = array ();
		$salon_model = new Calif_Salon ();
		
		/* Primera pasada, determinar si el NRC existe sobre siiau */
		foreach ($secciones_solicitadas as $seccion_solicitada) {
			$nrc = $seccion_solicitada->nrc;
			$observaciones_solicitadas[$nrc] = array ('edificio' => true, 'salon' => true, 'hora' => true, 'dias' => true, 'horas_bien' => false, 'match' => false, 'match_seccion' => false);
			
			$observaciones_solicitadas[$nrc]['inventado'] = false;
			if ($nrc > 60000) {
				$observaciones_solicitadas[$nrc]['inventado'] = true;
				$observaciones_solicitadas[$nrc]['existe'] = false;
				continue;
			}
			
			if (isset ($secciones[$nrc])) {
				$observaciones_solicitadas[$nrc]['existe'] = true;
			} else {
				$observaciones_solicitadas[$nrc]['existe'] = false;
				continue;
			}
			
			if ($observaciones[$nrc]['edificio'] === false) {
				$observaciones_solicitadas[$nrc]['edificio'] = false;
			}
			
			if ($observaciones[$nrc]['salon'] === false) {
				$observaciones_solicitadas[$nrc]['salon'] = false;
			}
			
			if ($observaciones[$nrc]['hora'] === false) {
				$observaciones_solicitadas[$nrc]['hora'] = false;
			}
			
			if ($observaciones[$nrc]['dias'] === false) {
				$observaciones_solicitadas[$nrc]['dias'] = false;
			}
			
			$observaciones_solicitadas[$nrc]['maestro'] = $observaciones[$nrc]['maestro'];
			
			if ($observaciones_solicitadas[$nrc]['dias'] === false ||
			    $observaciones_solicitadas[$nrc]['edificio'] === false ||
			    $observaciones_solicitadas[$nrc]['salon'] === false ||
			    $observaciones_solicitadas[$nrc]['hora'] === false) {
				/* No hacer chequeo de estas horas, según siiau tiene algún campo vacio */
				$observaciones_solicitadas[$nrc]['horas_bien'] = false;
				unset ($secciones[$nrc]);
				continue;
			}
			
			$observaciones_solicitadas[$nrc]['horas_bien'] = true;
			
			/* En caso de que exista, verificar que los horarios coincidan */
			$sql = new Gatuf_SQL ('nrc=%s', $nrc);
			$horarios_solicitados = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen()));
			
			foreach ($horarios_solicitados as $hora_seccion) {
				$salon_model->getSalonById ($hora_seccion->salon);
				
				$coincide = false;
				foreach ($horas_por_seccion[$nrc] as $hora) {
					if ($hora['inicio'] == $hora_seccion->hora_inicio &&
					    $hora['fin'] == $hora_seccion->hora_fin &&
					    $hora['aula'] == $salon_model->aula &&
					    $hora['edificio'] == $salon_model->edificio &&
					    $hora['lunes'] === $hora_seccion->lunes &&
					    $hora['martes'] === $hora_seccion->martes &&
					    $hora['miercoles'] === $hora_seccion->miercoles &&
					    $hora['jueves'] === $hora_seccion->jueves &&
					    $hora['viernes'] === $hora_seccion->viernes &&
					    $hora['sabado'] === $hora_seccion->sabado) {
						$coincide = true;
						break;
					}
				}
				
				if ($coincide === false) {
					$observaciones_solicitadas[$nrc]['horas_bien'] = false;
					break;
				}
			}
			/* Independientemente de si sus horas coincidieron,
			 * Eliminar esta sección de la lista de disponibles para match */
			unset ($secciones[$nrc]);
		} /* Foreach de la primera pasada */
		
		/* Para todas las secciones que no existen o son nrc inventados nuestros,
		 * Buscar una sección que coincida
		 * Por lo menos en horas */
		foreach ($secciones_solicitadas as $index_solicitado => $seccion_solicitada) {
			$nrc = $seccion_solicitada->nrc;
			
			if ($observaciones_solicitadas[$nrc]['existe']) continue;
			$observaciones_solicitadas[$nrc]['match'] = false;
			
			foreach ($secciones as $nrc_siiau => $seccion_siiau) {
				/* Si esta sección ofertada por siiau es diferente de la materia que la que buscamos,
				 * ignorarla */
				if ($seccion_siiau[0] != $seccion_solicitada->materia) continue;
				/* Ahora, tratar de hacer coincidir esta sección ofertada por siiau
				 * contra todos los horarios de nuestra sección solicitada */
				$match_por_horas = true;
				
				$sql = new Gatuf_SQL ('nrc=%s', $nrc);
				$horarios_solicitados = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen()));
				
				$total_coincide = true;
				foreach ($horarios_solicitados as $hora_seccion) {
					$salon_model->getSalonById ($hora_seccion->salon);
					
					$coincide = false;
					
					foreach ($horas_por_seccion[$nrc_siiau] as $hora) {
						if ($hora['inicio'] != $hora_seccion->hora_inicio ||
							$hora['fin'] != $hora_seccion->hora_fin ||
							$hora['aula'] != $salon_model->aula ||
							$hora['edificio'] != $salon_model->edificio ||
							$hora['lunes'] !== $hora_seccion->lunes ||
							$hora['martes'] !== $hora_seccion->martes ||
							$hora['miercoles'] !== $hora_seccion->miercoles ||
							$hora['jueves'] !== $hora_seccion->jueves ||
							$hora['viernes'] !== $hora_seccion->viernes ||
							$hora['sabado'] !== $hora_seccion->sabado) {
							$total_coincide = false;
						}
						if ($hora['inicio'] == $hora_seccion->hora_inicio &&
							$hora['fin'] == $hora_seccion->hora_fin &&
							$hora['lunes'] === $hora_seccion->lunes &&
							$hora['martes'] === $hora_seccion->martes &&
							$hora['miercoles'] === $hora_seccion->miercoles &&
							$hora['jueves'] === $hora_seccion->jueves &&
							$hora['viernes'] === $hora_seccion->viernes &&
							$hora['sabado'] === $hora_seccion->sabado) {
							$coincide = true;
							break;
						}
					}
					
					if ($coincide === false) {
						$match_por_horas = false;
						break;
					}
				}
				if ($total_coincide) {
					$observaciones_solicitadas[$nrc]['horas_bien'] = true;
				} else {
					$observaciones_solicitadas[$nrc]['horas_bien'] = false;
				}
				
				if ($match_por_horas) {
					$observaciones_solicitadas[$nrc]['match'] = true;
					$observaciones_solicitadas[$nrc]['match_nrc'] = $nrc_siiau;
					
					/* Antes, jalar las observaciones */
					if ($observaciones[$nrc_siiau]['edificio'] === false) {
						$observaciones_solicitadas[$nrc]['edificio'] = false;
					}
					
					if ($observaciones[$nrc_siiau]['salon'] === false) {
						$observaciones_solicitadas[$nrc]['salon'] = false;
					}
					
					$observaciones_solicitadas[$nrc]['maestro'] = $observaciones[$nrc_siiau]['maestro'];
					
					/* Eliminar el nrc de siiau, no debería hacer Match con otra */
					unset ($secciones[$nrc_siiau]);
					break;
				}
			}
		} /* Foreach de la busqueda de los inexistentes */
		
		/* Tercera pasada
		 * Buscar si la clave-seccion coinciden
		 * Debería ser más fácil, pues no se comparan horarios */
		foreach ($secciones_solicitadas as $index_solicitado => $seccion_solicitada) {
			$nrc = $seccion_solicitada->nrc;
			
			if ($observaciones_solicitadas[$nrc]['existe']) continue;
			if ($observaciones_solicitadas[$nrc]['match']) continue;
			
			foreach ($secciones as $nrc_siiau => $seccion_siiau) {
				if ($seccion_siiau[0] != $seccion_solicitada->materia) continue;
				
				if ($seccion_siiau[1] == $seccion_solicitada->seccion) {
					/* Esta sección de siiau hace match de la clave y la materia */
					$observaciones_solicitadas[$nrc]['match_seccion'] = true;
					$observaciones_solicitadas[$nrc]['match_nrc'] = $nrc_siiau;
					
					/* Jalar sus observaciones de siiau */
					if ($observaciones[$nrc_siiau]['edificio'] === false) {
						$observaciones_solicitadas[$nrc]['edificio'] = false;
					}
			
					if ($observaciones[$nrc_siiau]['salon'] === false) {
						$observaciones_solicitadas[$nrc]['salon'] = false;
					}
			
					if ($observaciones[$nrc_siiau]['hora'] === false) {
						$observaciones_solicitadas[$nrc]['hora'] = false;
					}
			
					if ($observaciones[$nrc_siiau]['dias'] === false) {
						$observaciones_solicitadas[$nrc]['dias'] = false;
					}
					$observaciones_solicitadas[$nrc]['maestro'] = $observaciones[$nrc_siiau]['maestro'];
					/* Eliminar el nrc de siiau, no debería hacer Match con otra */
					unset ($secciones[$nrc_siiau]);
				}
			}
		}
		return $observaciones_solicitadas;
	} /* Fin del save */
} /* Fin de la clase */
