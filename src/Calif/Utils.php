<?php
function Calif_Utils_arreglar_n ($cadena) {
	return str_replace ('~', 'ñ', $cadena);
}

function Calif_Utils_agregar_materia (&$materias, $clave, $descripcion) {
	$clave = strtoupper ($clave);
	
	if (isset ($materias [$clave])) return;
	
	$materias [$clave] = ucwords (strtolower (Calif_Utils_arreglar_n ($descripcion)));
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
	
	$alumnos [$codigo] = array (0 => $nombre, 1 => $apellido, 2 => $carrera);
}

function Calif_Utils_agregar_seccion (&$secciones, $nrc, $materia, $seccion, $maestro) {
	settype ($nrc, 'string');
	if (isset ($secciones [$nrc])) return;
	
	$secciones [$nrc] = array (0 => $materia, 1 => $seccion, 2 => $maestro);
}

function Calif_Utils_agregar_salon (&$salones, $edificio, $aula, $cupo) {
	if (isset ($salones[$edificio]) && isset ($salones[$edificio][$aula])) return;
	
	if (!isset ($salones[$edificio])) {
		$salones[$edificio] = array ();
	}
	
	$salones [$edificio][$aula] = $cupo;
}

function Calif_Utils_displayHoraSiiau ($val, $fix55 = true) {
	settype ($val, 'integer');
	
	if ($fix55 && $val % 100 == 55) {
		$val += 45;
	}
	
	$parte_minutos = $val % 100;
	$parte_horas = ($val - $parte_minutos) / 100;
	
	return sprintf ('%02s:%02s', $parte_horas, $parte_minutos);
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
	if (count ($edificios) != 0) {
		$sql = new Gatuf_SQL ('edificio IN ('.implode (',', array_fill (0, count ($edificios), '%s')).')', $edificios);
		$where = $sql->gen ();
	} else {
		$where = '';
	}
	$salones = Gatuf::factory('Calif_Salon')->getList (array ('order' => array ('edificio ASC', 'aula ASC'), 'filter' => $where));
	
	if (count ($salones) == 0)  return array ();
	
	$libres = array ();
	foreach ($salones as $salon) {
		$libres[$salon->id] = $salon;
	}
	
	foreach ($semana as $dia) {
		$sql = new Gatuf_SQL (sprintf ('%s=1', $dia));
		$horarios_en_dia = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen ()));
	
		foreach ($horarios_en_dia as $hora) {
			if (($bus_inicio >= $hora->hora_inicio && $bus_inicio < $hora->hora_fin) ||
			    ($bus_fin > $hora->hora_inicio && $bus_fin <= $hora->hora_fin) ||
			    ($bus_inicio <= $hora->hora_inicio && $bus_fin >= $hora->hora_fin)) {
				/* Choque, este salon está ocupado a la hora solicitada */
				if (isset ($libres[$hora->salon])) unset ($libres[$hora->salon]);
			}
		}
	}
	
	return $libres;
}
