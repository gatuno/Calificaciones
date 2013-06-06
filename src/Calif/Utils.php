<?php
function Calif_Utils_arreglar_n ($cadena) {
	return str_replace ("~", "ñ", $cadena);
}

function Calif_Utils_agregar_materia (&$materias, $clave, $descripcion) {
	$clave = strtoupper ($clave);
	
	if (isset ($materias [$clave])) return;
	
	$materias [$clave] = ucwords (strtolower (Calif_Utils_arreglar_n ($descripcion)));
}

function Calif_Utils_agregar_maestro (&$maestros, $linea) {
	$explote = explode (" ", $linea);
	
	$n = count ($explote);
	
	$codigo = trim ($explote [($n - 1)], "()");
	settype ($codigo, "string");
	if (isset ($maestros [$codigo])) {
		unset ($explote);
		return $codigo;
	}
	
	/* Separar los campos */
	if ($n == 3) {
		/* Sólo un nombre y código */
		$nombre = $explote[0];
		$apellido = $explote[1];
	} else if ($n == 4) {
		$nombre = $explote[0];
		$apellido = $explote[1] . " " . $explote[2];
	} else if ($n == 5) {
		/* Lo normal */
		$nombre = $explote[0] . " " . $explote[1];
		$apellido = $explote[2] . " " . $explote[3];
	} else if ($n == 6) {
		$nombre = $explote[0] . " " . $explote[1] . " " . $explote[2];
		$apellido = $explote[3] . " " . $explote[4];
	} else if ($n == 7) {
		$nombre = $explote[0] . " " . $explote[1] . " " . $explote[2];
		$apellido = $explote[3] . " " . $explote[4] . " " . $explote[5];
	} else {
		$nombre = "";
		$apellido = "";
		
		$mitad = ($n - 1) / 2;
		for ($g = 0; $g < $mitad; $g++) {
			$nombre = $nombre . " " . $explote[$g];
		}
		
		for ($g = $mitad; $g < ($n - 1); $g++) {
			$apellido = $apellido . " " . $explote[$g];
		}
		$nombre = trim ($nombre, " ");
		$apellido = trim ($apellido, " ");
	}
	
	if (!isset ($nombre) || !isset ($apellido)) {
		echo "Advertencia, Maestro incorrecto\n";
		echo "La linea causante es: \"" . $linea . "\"\n";
	}
	$nombre = ucwords (strtolower (Calif_Utils_arreglar_n ($nombre)));
	$apellido = ucwords (strtolower (Calif_Utils_arreglar_n ($apellido)));
	
	$maestros [$codigo] = array (0 => $nombre, 1 => $apellido);
	
	unset ($explote);
	return $codigo;
}

function Calif_Utils_agregar_alumno (&$alumnos, &$carreras, $codigo, $linea, $carrera) {
	settype ($codigo, "string");
	if (isset ($alumnos [$codigo])) return;
	
	$explote = explode (",", $linea);
	if (!isset ($explote [1])) {
		echo "Advertencia, alumno con nombre raro\n";
		echo "El alumno en cuestión es: \"" . $linea . "\"\n";
	}
	$apellido = trim (ucwords (strtolower (Calif_Utils_arreglar_n ($explote[0]))));
	$nombre = trim (ucwords (strtolower (Calif_Utils_arreglar_n ($explote[1]))));
	$carrera = trim (strtoupper ($carrera));
	
	/* Si la carrera no existe, agregarla */
	if (!isset ($carreras [$carrera])) {
		$carreras [$carrera] = "Una carrera con clave " . $carrera;
	}
	
	$alumnos [$codigo] = array (0 => $nombre, 1 => $apellido, 2 => $carrera);
}

function Calif_Utils_agregar_seccion (&$secciones, $nrc, $materia, $seccion, $maestro) {
	settype ($nrc, "string");
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
	
	$parte_minutos = $val % 100;
	$parte_horas = ($val - $parte_minutos) / 100;
	
	return sprintf ('%02s:%02s', $parte_horas, $parte_minutos);
}

function Calif_Utils_importsiiau ($form_field) {
	$ruta = $form_field['tmp_name'];
	
	if (($archivo = fopen ($ruta, "r")) === false) {
		throw new Exception ('Falló al abrir el archivo '.$ruta);
	}
		
	$materias = array ();
	$alumnos = array ();
	$maestros = array ();
	$secciones = array ();
	$carreras = array ();
	
	/* Primera pasada, llenar los arreglos */
	while (($linea = fgetcsv ($archivo, 400, ",", "\"")) !== FALSE) {
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

function Calif_Utils_importoferta ($form_field) {
	$ruta = $form_field['tmp_name'];
	
	if (($archivo = fopen ($ruta, "r")) === false) {
		throw new Exception ('Falló al abrir el archivo '.$ruta);
	}
	
	$con = &Gatuf::db();
	
	$seccion_model = new Calif_Seccion ();
	$req = sprintf ('TRUNCATE TABLE Calificaciones'); /* FIXME: prefijo de la tabla */
	$con->execute ($req);
	
	/* Ya no borrar todos los horarios
	$horario_model = new Calif_Horario ();
	$req = sprintf ('TRUNCATE TABLE %s', $horario_model->getSqlTable());
	$con->execute ($req);
	 */
	
	$req = sprintf ('TRUNCATE TABLE %s', $seccion_model->getGruposSqlTable());
	$con->execute ($req);
	
	/* Tampoco borrar todos los NRC
	$req = sprintf ('TRUNCATE TABLE %s', $seccion_model->getSqlTable ());
	$con->execute ($req);
	 */
	
	/* Tampoco borrar todas las aulas existentes
	$salon_model = new Calif_Salon ();
	$req = sprintF ('TRUNCATE TABLE %s', $salon_model->getSqlTable ());
	$con->execute ($req);
	 */
	
	$maestro = new Calif_Maestro ();
	
	if (false === $maestro->getMaestro ('1111111')) {
		$maestro->codigo = '1111111';
		$maestro->nombre = 'Staff';
		$maestro->apellido = 'Staff Staff';
		$maestro->correo = '';
		
		$maestro->create ();
	}
	
	$materias = array ();
	$secciones = array ();
	$salones = array ();
	$edificios = array ();
	
	$nrc_vacio = 40000;
	/* Primera pasada, llenar los arreglos */
	while (($linea = fgetcsv ($archivo, 400, ",", "\"")) !== FALSE) {
		$no_campos = count ($linea);
		
		if ($no_campos < 17) {
			continue;
		}
		
		if ($linea[0] === '') {
			throw new Exception ('Alto: NRC vacio. Cerca de la materia '.$linea[2].' con sección '.$linea[4]);
		}
		
		Calif_Utils_agregar_materia ($materias, $linea[2], $linea[3]);
		Calif_Utils_agregar_seccion ($secciones, $linea[0], $linea[2], $linea[4], '1111111');
		Calif_Utils_agregar_salon ($salones, $linea [14], $linea [15], $linea[5]);
	}
	
	$materia_model = new Calif_Materia ();
	foreach ($materias as $clave => $descripcion) {
		if ($materia_model->getMateria ($clave) === false) {
			$materia_model->clave = $clave;
			$materia_model->descripcion = $descripcion;
			
			$materia_model->create ();
		}
	}
	
	$seccion_model = new Calif_Seccion ();
	foreach ($secciones as $nrc => $value) {
		if ($seccion_model->getNrc ($nrc) === false) {
			/* El NRC no existe, crearlo */
			$seccion_model->nrc = $nrc;
			$seccion_model->materia = $value[0];
			$seccion_model->seccion = $value[1];
			$seccion_model->maestro = $value[2];
			
			$seccion_model->create ();
		} else {
			$req = sprintf ('INSERT INTO NRC_Duplicados (nrc) VALUES (%s)', $seccion_model->nrc);
			$con->execute ($req);
			
			/* En el caso de que este NRC ya exista, eliminar todos su horarios */
			$sql = new Gatuf_SQL ('nrc=%s', $seccion_model->nrc);
			$horas = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen()));
			
			foreach ($horas as $hora) {
				$hora->delete ();
			}
		}
	}
	$salon_model = new Calif_Salon ();
	$edificio_model = new Calif_Edificio ();
	ksort ($salones);
	foreach ($salones as $edificio => &$aulas) {
		if (false === $edificio_model->getEdificio ($edificio)) {
			$edificio_model->clave = $edificio;
			$edificio_model->descripcion = 'Módulo '.$edificio;
			$edificio_model->create ();
		}
		ksort ($aulas);
		foreach ($aulas as $aula => &$cupo) {
			if ($salon_model->getSalon ($edificio, $aula) === false) {
				$salon_model->edificio = $edificio;
				$salon_model->aula = $aula;
				$salon_model->cupo = $cupo;
				
				$salon_model->create ();
			}
			
			$cupo = $salon_model->id;
		}
	}
	
	rewind ($archivo);
	
	$horario_model = new Calif_Horario ();
	
	$nrc_vacio = 40000;
	/* Segunda pasada, crear los horarios */
	while (($linea = fgetcsv ($archivo, 400, ",", "\"")) !== FALSE) {
		$no_campos = count ($linea);
		
		if ($no_campos < 17) {
			continue;
		}
		
		if ($linea[0] === '') {
			throw new Exception ("Alto, NRC Vacio");
		}
		
		$horario_model->nrc = $linea[0];
		$horario_model->hora_inicio = $linea[6];
		$horario_model->hora_fin = $linea[7];
		$horario_model->salon = $salones[$linea[14]][$linea[15]];
		$horario_model->lunes = $linea[8];
		$horario_model->martes = $linea[9];
		$horario_model->miercoles = $linea[10];
		$horario_model->jueves = $linea[11];
		$horario_model->viernes = $linea[12];
		$horario_model->sabado = $linea[13];
		
		$horario_model->create ();
	}
	
	fclose ($archivo);
}

