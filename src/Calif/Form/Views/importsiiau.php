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
				if ($seccion_model->get ($linea[$cabecera['nrc']]) === false) continue;
				/* TODO: Verificar que el nrc coincida con la materia y la sección */
				$secciones[$linea[$cabecera['nrc']]] = clone ($seccion_model);
			}
			
			Calif_Utils_agregar_alumno ($alumnos, $carreras, $linea[$cabecera['cod_al']], $linea[$cabecera['alumno']], $linea[$cabecera['car_al']]);
		}
		
		/* Crear todo lo que sea necesario */
		$carrera_model = new Calif_Carrera ();
		
		foreach ($carreras as $clave => $descripcion) {
			if ($carrera_model->get($clave) === false) {
				$carrera_model->clave = $clave;
				$carrera_model->descripcion = $descripcion;
				
				$carrera_model->create ();
			}
		}
		
		$alumno_model = new Calif_Alumno ();
		$user_model = new Calif_User ();
		foreach ($alumnos as $codigo => $value) {
			if ($alumno_model->get ($codigo) === false) {
				$alumno_model->codigo = $codigo;
				$alumno_model->nombre = $value['nombre'];
				$alumno_model->apellido = $value['apellido'];
				
				$user_model->login = $codigo;
				$user_model->email = '';
				$user_model->type = 'a';
				$user_model->administrator = false;
				$alumno_model->user = $user_model;
				
				$alumno_model->create ();
				$user_model->create ();
			}
		}
		
		unset ($carreras);
		unset ($alumnos);
		
		rewind ($archivo);
		
		/* Borrar los grupos y por ende las calificaciones */
		$hay = array(strtolower('Calif_Alumno'), strtolower('Calif_Seccion'));
		if (isset ($GLOBALS['_GATUF_models_related'][$hay[0]][$hay[1]])) {
			// La relación la tiene el $hay[1]
			$dbname = $this->_con->dbname;
			$dbpfx = $this->_con->pfx;
		} else {
			$dbname = $seccion_model->_con->dbname;
			$dbpfx = $seccion_model->_con->pfx;
		}
		sort($hay);
		$grupos_tabla = $dbname.'.'.$dbpfx.$hay[0].'_'.$hay[1].'_assoc';
		
		$req = sprintf ('TRUNCATE TABLE %s', $grupos_tabla);
		$con->execute ($req);
		
		/* Cambiar el motor a MEMORY */
		$req = sprintf ('ALTER TABLE %s ENGINE = MEMORY', $grupos_tabla);
		$con->execute ($req);
		
		while (($linea = fgetcsv ($archivo, 400, ',', '"')) !== false) {
			$no_campos = count ($linea);
			if ($no_campos < 20) continue;
			
			if (!isset ($secciones [$linea[$cabecera['nrc']]])) continue;
			$alumno_model->get ($linea[$cabecera['cod_al']]);
			$secciones[$linea[$cabecera['nrc']]]->setAssoc ($alumno_model);
		}
		
		/* Cambiar el motor a INNODB */
		$req = sprintf ('ALTER TABLE %s ENGINE = INNODB', $grupos_tabla);
		$con->execute ($req);
		
		fclose ($archivo);
	}
}
