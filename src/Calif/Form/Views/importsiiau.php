<?php

class Calif_Form_Views_importsiiau extends Gatuf_Form {
	public function initFields($extra=array()) {
		Gatuf::loadFunction ('Calif_Utils_dontmove');
		
		$this->fields['oferta'] = new Gatuf_Form_Field_File (
			array('label' => 'Seleccionar archivo',
				'help_text' => 'El archivo de la oferta de siiau',
				'move_function_params' => array(),
				'max_size' => 10485760,
				'move_function' => 'Calif_Utils_dontmove'
		));
	}
	
	public function save ($commit = true) {
		Gatuf::loadFunction ('Calif_Utils_detectarColumnas');
		
		$ruta = $this->data['oferta']['tmp_name'];
		
		if (($archivo = fopen ($ruta, "r")) === false) {
			throw new Exception ('Falló al abrir el archivo '.$ruta);
		}
	
		$con = &Gatuf::db();
		
		/* Detectar cabeceras */
		$linea = fgetcsv ($archivo, 600, ',', '"');
		
		if ($linea === false || is_null ($linea)) {
			throw new Exception ('No hay cabecera, o es una linea vacia');
		}
		
		$cabecera = Calif_Utils_detectarColumnas ($linea);
		
		/* Verificar que existan los campos necesarios */
		if (!isset ($cabecera['nrc'])) {
			/* Se requiere una columna de NRC */
			throw new Gatuf_Form_Invalid ('El archivo importado no una columna de nrc');
		}
		
		if (!isset ($cabecera['cod_al']) || !isset ($cabecera['alumno']) || !isset ($cabecera['car_al'])) {
			/* Se requiere columna de código, nombre y carrera del alumno */
			throw new Gatuf_Form_Invalid ('El archivo importado no una columna de codigo, nombre o carrera del alumno (cod_al, alumno, car_al)');
		}
		
		$alumnos = array ();
		$secciones = array ();
		$carreras = array ();
	
		$seccion_model = new Calif_Seccion ();
	
		/* Primera pasada, llenar los arreglos */
		while (($linea = fgetcsv ($archivo, 400, ',', '"')) !== FALSE) {
			$no_campos = count ($linea);
			
			if ($no_campos < 20) {
				continue;
			}
			if (!isset ($secciones[$linea[$cabecera['nrc']]])) {
				if ($seccion_model->getNrc ($linea[$cabecera['nrc']]) === false) continue;
				/* TODO: Verificar que el nrc coincida con la materia y la sección */
				$secciones[$linea[$cabecera['nrc']]] = clone ($seccion_model);
			}
			
			Calif_Utils_agregar_alumno ($alumnos, $carreras, $linea[$cabecera['cod_al']], $linea[$cabecera['alumno']], $linea[$cabecera['car_al']]);
		}
		
		/* Crear todo lo que sea necesario */
		$carrera_model = new Calif_Carrera ();
		
		foreach ($carreras as $clave => $descripcion) {
			if ($carrera_model->getCarrera($clave) === false) {
				$carrera_model->clave = $clave;
				$carrera_model->descripcion = $descripcion;
				
				$carrera_model->create ();
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
		
		unset ($carreras);
		unset ($alumnos);
		
		rewind ($archivo);
		
		$req = sprintf ('TRUNCATE TABLE Calificaciones'); /* FIXME: prefijo de la tabla */
		//$con->execute ($req);
		
		$req = sprintf ('TRUNCATE TABLE Grupos'); /* FIXME: prefijo de la tabla */
		//$con->execute ($req);
		
		$req = 'CREATE TABLE Grupos_RAM (`nrc` INT( 5 ) unsigned zerofill NOT NULL, `alumno` CHAR( 9 ) NOT NULL) ENGINE = MEMORY DEFAULT CHARSET=utf8';
		
		$con->execute ($req);
		
		while (($linea = fgetcsv ($archivo, 400, ',', '"')) !== false) {
			$no_campos = count ($linea);
			if ($no_campos < 20) continue;
			
			if (!isset ($secciones [$linea[$cabecera['nrc']]])) continue;
			$secciones[$linea[$cabecera['nrc']]]->addAlumnoToRam ('Grupos_RAM', $linea[$cabecera['cod_al']]);
		}
		$req = 'ALTER IGNORE TABLE Grupos_RAM ADD UNIQUE INDEX (nrc, alumno);';
		$con->execute ($req);
		
		/* Copiar todos los datos de RAM a la tabla */
		$req = sprintf ('INSERT INTO Grupos SELECT * FROM Grupos_RAM');
		
		$con->execute ($req);
		
		$req = 'DROP TABLE Grupos_RAM';
		
		$con->execute ($req);
		
		fclose ($archivo);
	}
}
