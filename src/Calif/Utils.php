<?php
function Calif_Utils_arreglar_n ($cadena) {
	return str_replace ('~', 'ñ', $cadena);
}

function Calif_Utils_map_departamento ($depa) {
	static $departamentos = array ("DEPTO. DE CIENCIAS COMPUTACIONALES" => 1500,
		"DEPTO. DE ELECTRONICA" => 1510,
		"DEPTO. DE FARMACOBIOLOGIA" => 1360,
		"DEPTO. DE FISICA" => 1370,
		"DEPTO. DE INGENIERIA CIVIL Y TOPOGRAFIA" => 1420,
		"DEPTO. DE INGENIERIA DE PROYECTOS" => 1460,
		"DEPTO. DE INGENIERIA INDUSTRIAL" => 1440,
		"DEPTO. DE INGENIERIA MECANICA ELECTRICA" => 1450,
		"DEPTO. DE INGENIERIA QUIMICA" => 1470,
		"DEPTO. DE MADERA CELULOSA Y PAPEL" => 1480,
		"DEPTO. DE MATEMATICAS" => 1390,
		"DEPTO. DE QUIMICA" => 1400,
	);
	$depa = trim ($depa, ' ');
	if (isset ($departamentos[$depa])) return $departamentos[$depa];
	return 0;
}

function Calif_Utils_agregar_materia (&$materias, $clave, $descripcion, $departamento = "", $creditos = 0) {
	$clave = strtoupper ($clave);
	
	if (isset ($materias [$clave])) return;
	
	$desc = ucwords (strtolower (Calif_Utils_arreglar_n ($descripcion)));
	$depa_num = Calif_Utils_map_departamento ($departamento);
	$materias [$clave] = array ('clave' => $clave, 'descripcion' => $desc, 'departamento' => $depa_num, 'creditos' => $creditos);
}

function Calif_Utils_agregar_maestro (&$maestros, $linea, $vacio=1111111) {
	$codigo = trim (strstr ($linea, '('), '() ');
	$nombre_completo = trim (strstr ($linea, '(', true), ' ');
	
	if ($codigo == '') {
		/* Oops, código vacio */
		$codigo = $vacio;
		
		$nombre_completo = 'Staff, Staff Staff';
	}
	
	settype ($codigo, "string");
	if (isset ($maestros [$codigo])) {
		return $codigo;
	}
	
	if (strpos ($nombre_completo, ',') === false) {
		/* Separar los campos manualmente */
		$explote = explode (" ", $nombre_completo);
		
		$n = count ($explote);
		
		if ($n == 3) {
			/* Sólo un nombre y código */
			$nombre = $explote[0];
			$apellido = $explote[1];
		} else if ($n == 4) {
			$nombre = $explote[0];
			$apellido = $explote[1].' '.$explote[2];
		} else if ($n == 5) {
			/* Lo normal */
			$nombre = $explote[0].' '.$explote[1];
			$apellido = $explote[2].' '.$explote[3];
		} else if ($n == 6) {
			$nombre = $explote[0].' '.$explote[1].' '.$explote[2];
			$apellido = $explote[3].' '.$explote[4];
		} else if ($n == 7) {
			$nombre = $explote[0].' '.$explote[1].' '.$explote[2];
			$apellido = $explote[3].' '.$explote[4].' '.$explote[5];
		} else {
			$nombre = '';
			$apellido = '';
		
			$mitad = ($n - 1) / 2;
			for ($g = 0; $g < $mitad; $g++) {
				$nombre = $nombre.' '.$explote[$g];
			}
		
			for ($g = $mitad; $g < ($n - 1); $g++) {
				$apellido = $apellido.' '.$explote[$g];
			}
			$nombre = trim ($nombre, ' ');
			$apellido = trim ($apellido, ' ');
		}
	} else {
		$nombre = trim (strstr ($nombre_completo, ','), ', ');
		$apellido = trim (strstr ($nombre_completo, ',', true), ', ');
	}
	
	
	if (!isset ($nombre) || !isset ($apellido)) {
		throw new Exception ('Nombre del maestro raro. El dato en cuestión es: '.$linea);
	}
	$nombre = ucwords (strtolower (Calif_Utils_arreglar_n ($nombre)));
	$apellido = ucwords (strtolower (Calif_Utils_arreglar_n ($apellido)));
	
	$maestros [$codigo] = array (0 => $nombre, 1 => $apellido);
	
	unset ($explote);
	return $codigo;
}

function Calif_Utils_agregar_alumno (&$alumnos, &$carreras, $codigo, $linea, $carrera) {
	settype ($codigo, 'string');
	if (isset ($alumnos [$codigo])) return;
	
	$explote = explode (',', $linea);
	if (!isset ($explote [1])) {
		throw new Exception ('Nombre del alumno raro. El dato en cuestión es: '.$linea);
	}
	$apellido = trim (ucwords (strtolower (Calif_Utils_arreglar_n ($explote[0]))));
	$nombre = trim (ucwords (strtolower (Calif_Utils_arreglar_n ($explote[1]))));
	$carrera = trim (strtoupper ($carrera));
	
	/* Si la carrera no existe, agregarla */
	if (!isset ($carreras [$carrera])) {
		$carreras [$carrera] = 'Una carrera con clave ' . $carrera;
	}
	
	$alumnos [$codigo] = array ('codigo' => $codigo, 'nombre' => $nombre, 'apellido' => $apellido, 'carrera' => $carrera);
}

function Calif_Utils_agregar_seccion (&$secciones, $nrc, $materia, $seccion, $maestro) {
	settype ($nrc, 'string');
	if (isset ($secciones [$nrc])) return;
	
	$secciones [$nrc] = array ('nrc' => $nrc, 'materia' => $materia, 'seccion' => $seccion, 'maestro' => $maestro);
}

function Calif_Utils_agregar_salon (&$salones, $edificio, $aula, $cupo) {
	if (isset ($salones[$edificio]) && isset ($salones[$edificio][$aula])) return;
	
	if (!isset ($salones[$edificio])) {
		$salones[$edificio] = array ();
	}
	
	$salones [$edificio][$aula] = $cupo;
}

function Calif_Utils_displayHora ($val) {
	$explote = explode (':', $val);
	$minuto = (int) $explote[1];
	$hora = (int) $explote[0];
	
	if ($minuto > 50) {
		$minuto = 0;
		$hora++;
	}
	
	return sprintf ('%02s:%02s', $hora, $minuto);
}

function Calif_Utils_horaFromSiiau ($val) {
	settype ($val, 'integer');
	
	$parte_minutos = $val % 100;
	$parte_horas = ($val - $parte_minutos) / 100;
	
	return $parte_horas.':'.$parte_minutos;
}

function Calif_Utils_detectarColumnas ($cabecera) {
	$indices = array ();
	
	foreach ($cabecera as $index => $columna) {
		$col = strtolower ($columna);
		
		if (!isset ($indices[$col])) {
			$indices[$col] = $index;
		}
	}
	
	return $indices;
}

function Calif_Utils_dontmove ($field) {
	/* Solo no mover el archivo */
}

function Calif_Utils_buscarSalonVacio ($semana, $bus_inicio, $bus_fin, $edificios = array ()) {
	/* FIXME: Optimizar este código */
	if (count ($edificios) != 0) {
		$sql = new Gatuf_SQL ('edificio IN ('.implode (',', array_fill (0, count ($edificios), '%s')).')', $edificios);
		$where = $sql->gen ();
	} else {
		$where = null;
	}
	$salones = Gatuf::factory('Calif_Salon')->getList (array ('order' => array ('edificio ASC', 'aula ASC'), 'filter' => $where));
	
	if (count ($salones) == 0)  return array ();
	
	$libres = array ();
	foreach ($salones as $salon) {
		$libres[$salon->id] = $salon;
	}
	
	$horario_model = new Calif_Horario ();
	$horario_model->inicio = $bus_inicio;
	$horario_model->fin = $bus_fin;
	
	foreach ($semana as $dia) {
		$sql = new Gatuf_SQL (sprintf ('%s=1', $dia));
		$horario_model->$dia = true;
		$horarios_en_dia = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen ()));
	
		foreach ($horarios_en_dia as $hora) {
			if (Calif_Horario::chocan ($horario_model, $hora)) {
				/* Choque, este salon está ocupado a la hora solicitada */
				if (isset ($libres[$hora->salon])) unset ($libres[$hora->salon]);
			}
		}
		$horario_model->$dia = false;
	}
	
	return $libres;
}

function Calif_Utils_errorHoras( $seccion = false ){
			$horas = 0;
			$materia = new Calif_Materia ($seccion->materia);
			$horarios = $seccion->get_calif_horario_list ();
			$materia = new Calif_Materia ($seccion->materia);
			foreach($horarios as $h){
				$dias = ($h->l + $h->m + $h->i + $h->j + $h->v + $h->s + $h->d );
				$datetime1 = new DateTime( $h->inicio );
				$datetime2 = new DateTime( $h->fin );
				$interval = $datetime1->diff($datetime2);
				if((int)$interval->format('%i') > 50 )
					$horas += $dias;
					$horas += (int)$interval->format('%h')*$dias;
			}
			$error = (abs ( $horas - ($materia->teoria + $materia->practica ) ) )?true:false;
			return $error;
}
