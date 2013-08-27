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

function Calif_Utils_displayHoraSiiau ($val) {
	settype ($val, 'integer');
	
	if ($val % 100 == 55) {
		$val += 45;
	}
	
	$parte_minutos = $val % 100;
	$parte_horas = ($val - $parte_minutos) / 100;
	
	return sprintf ('%02s:%02s', $parte_horas, $parte_minutos);
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

function Calif_Utils_importsiiau ($form_field) {
	$ruta = $form_field['tmp_name'];
	
	if (($archivo = fopen ($ruta, 'r')) === false) {
		throw new Exception ('Falló al abrir el archivo '.$ruta);
	}
		
	$materias = array ();
	$alumnos = array ();
	$maestros = array ();
	$secciones = array ();
	$carreras = array ();
	
	/* Primera pasada, llenar los arreglos */
	while (($linea = fgetcsv ($archivo, 400, ',', '"')) !== FALSE) {
		$no_campos = count ($linea);
		
		if ($no_campos < 20) {
			continue;
		}
		
		Calif_Utils_agregar_materia ($materias, $linea[1], $linea[2]);
		$codigo_del_maestro = Calif_Utils_agregar_maestro ($maestros, $linea[16]);
		Calif_Utils_agregar_alumno ($alumnos, $carreras, $linea[17], $linea[18], $linea[19]);
		Calif_Utils_agregar_seccion ($secciones, $linea[0], $linea[1], $linea[3], $codigo_del_maestro);
	}
	$con = &Gatuf::db();
	
	/* Crear todo lo que sea necesario */
	$carrera_model = new Calif_Carrera ();
	
	foreach ($carreras as $clave => $descripcion) {
		if ($carrera_model->getCarrera($clave) === false) {
			$carrera_model->clave = $clave;
			$carrera_model->descripcion = $descripcion;
			
			$carrera_model->create ();
		}
	}
	
	$materia_model = new Calif_Materia ();
	foreach ($materias as $clave => $descripcion) {
		if ($materia_model->getMateria ($clave) === false) {
			$materia_model->clave = $clave;
			$materia_model->descripcion = $descripcion;
			
			$materia_model->create ();
		}
	}
	
	$maestro_model = new Calif_Maestro ();
	foreach ($maestros as $codigo => $value) {
		if ($maestro_model->getMaestro ($codigo) === false) {
			$maestro_model->codigo = $codigo;
			$maestro_model->nombre = $value[0];
			$maestro_model->apellido = $value[1];
			$maestro_model->correo = '';
			
			$maestro_model->create ();
		}
	}
	
	$alumno_model = new Calif_Alumno ();
	foreach ($alumnos as $codigo => $value) {
		if ($alumno_model->getAlumno ($codigo) === false) {
			$alumno_model->codigo = $codigo;
			$alumno_model->nombre = $value[0];
			$alumno_model->apellido = $value[1];
			$alumno_model->carrera = $value[2];
			$alumno_model->correo = '';
			
			$alumno_model->create ();
		}
	}
	$todos_los_nrc = array ();
	$seccion_model = new Calif_Seccion ();
	foreach ($secciones as $nrc => $value) {
		if ($seccion_model->getNrc ($nrc) === false) {
			$seccion_model->nrc = $nrc;
			$seccion_model->materia = $value[0];
			$seccion_model->seccion = $value[1];
			$seccion_model->maestro = $value[2];
			
			$seccion_model->create ();
		}
		$todos_los_nrc [$nrc] = clone ($seccion_model);
	}
	
	unset ($materias);
	unset ($carreras);
	unset ($alumnos);
	unset ($secciones);
	
	rewind ($archivo);
	
	$req = sprintf ('TRUNCATE TABLE Calificaciones'); /* FIXME: prefijo de la tabla */
	$con->execute ($req);
	
	$req = sprintf ('TRUNCATE TABLE %s', $seccion_model->getGruposSqlTable());
	$con->execute ($req);
	
	$req = 'CREATE TABLE Grupos_RAM (`nrc` INT( 5 ) NOT NULL, `alumno` CHAR( 9 ) NOT NULL) ENGINE = MEMORY';
	
	$con->execute ($req);
	
	while (($linea = fgetcsv ($archivo, 400, ',', '"')) !== false) {
		$no_campos = count ($linea);
		if ($no_campos < 20) continue;
		
		$todos_los_nrc[$linea[0]]->addAlumnoToRam ('Grupos_RAM', $linea[17]);
	}
	/* Copiar todos los datos de RAM a la tabla */
	
	$req = sprintf ('INSERT INTO %s SELECT * FROM Grupos_RAM', $seccion_model->getGruposSqlTable());
	
	$con->execute ($req);
	
	$req = 'DROP TABLE Grupos_RAM';
	
	$con->execute ($req);
	
	fclose ($archivo);
}

function Calif_Utils_dontmove ($field) {
	/* Solo no mover el archivo */
}

function Calif_Utils_buscarSalonVacio ($semana, $bus_inicio, $bus_fin) {
	$salones = Gatuf::factory('Calif_Salon')->getList (array ('order' => array ('edificio ASC', 'aula ASC')));
	
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
