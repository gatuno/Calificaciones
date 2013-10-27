<?php

class Calif_Form_Views_importoferta extends Gatuf_Form {
	public function initFields($extra=array()) {
		Gatuf::loadFunction ('Calif_Utils_dontmove');
		
		$this->fields['oferta'] = new Gatuf_Form_Field_File (
			array('label' => 'Seleccionar archivo',
				'help_text' => 'Su archivo separado por comas. (Estilo Monica Durón, estilo SIIAU)',
				'move_function_params' => array(),
				'max_size' => 10485760,
				'move_function' => 'Calif_Utils_dontmove'
		));
		
		$this->fields['materias'] = new Gatuf_Form_Field_Boolean (
			array ('label' => 'Importar materias',
				'help_text' => 'Si se deben importar las materias',
				'required' => true,
				'initial' => true,
				'widget' => 'Gatuf_Form_Widget_CheckboxInput'
		));
		
		$this->fields['nrcs'] = new Gatuf_Form_Field_Boolean (
			array ('label' => 'Importar secciones',
				'help_text' => 'Si se deben importar las secciones y NRCs del archivo. Implica importar las materias.',
				'required' => true,
				'initial' => true,
				'widget' => 'Gatuf_Form_Widget_CheckboxInput'
		));
		
		$this->fields['destruirnrcs'] = new Gatuf_Form_Field_Boolean (
			array ('label' => 'Destruir NRCs duplicados',
				'help_text' => 'Si se activa esta casilla y se importa un NRC que ya existe en el sistema, el viejo NRC será destruido y se tomarán los valores del archivo.',
				'required' => true,
				'initial' => true,
				'widget' => 'Gatuf_Form_Widget_CheckboxInput'
		));
		
		$this->fields['maestros'] = new Gatuf_Form_Field_Boolean (
			array ('label' => 'Importar profesores',
				'help_text' => 'Si se deben importar los nombres y códigos de los Profesores.',
				'required' => true,
				'initial' => true,
				'widget' => 'Gatuf_Form_Widget_CheckboxInput'
		));
		
		$this->fields['maestrosnrc'] = new Gatuf_Form_Field_Boolean (
			array ('label' => 'Importar profesores asignados a NRC',
				'help_text' => 'Se asignarán profesores a los NRC ya existentes. No se crearán NRCs a menos que active la casilla de crear NRCs. En caso contrario se asignará Staff a los profesores. Implica importar los profesores.',
				'required' => true,
				'initial' => true,
				'widget' => 'Gatuf_Form_Widget_CheckboxInput'
		));
				
		$this->fields['salones'] = new Gatuf_Form_Field_Boolean (
			array ('label' => 'Importar salones',
				'help_text' => 'Si se deben crear los salones y edificios del archivo',
				'required' => true,
				'initial' => true,
				'widget' => 'Gatuf_Form_Widget_CheckboxInput'
		));
		
		$this->fields['horarios'] = new Gatuf_Form_Field_Boolean (
			array ('label' => 'Importar horarios',
				'help_text' => 'Si se deben importar los horarios asociados con un NRC. Implica importar los salones y edificios. Se destruirán las horas previamente asignadas a los NRCs.',
				'required' => true,
				'initial' => true,
				'widget' => 'Gatuf_Form_Widget_CheckboxInput'
		));
	}
	
	function save ($commit=true) {
		Gatuf::loadFunction ('Calif_Utils_detectarColumnas');
		
		$ruta = $this->data['oferta']['tmp_name'];
		
		if (($archivo = fopen ($ruta, "r")) === false) {
			throw new Exception ('Falló al abrir el archivo '.$ruta);
		}
	
		$con = &Gatuf::db();
		
		/* Borrar los grupos y por ende las calificaciones */
		$hay = array(strtolower('Calif_Alumno'), strtolower('Calif_Seccion'));
		sort($hay);
		$grupos_tabla = $con->pfx.$hay[0].'_'.$hay[1].'_assoc';
		
		$req = sprintf ('TRUNCATE TABLE %s', $grupos_tabla);
		$con->execute ($req);
		
		$maestro_model = new Calif_Maestro ();
		$usuario_model = new Calif_User ();
		
		/* Asegurarnos que exista nuestro amigo Staff */
		if (false === $maestro_model->get ('1111111')) {
			$maestro_model->codigo = '1111111';
			$maestro_model->nombre = 'Staff';
			$maestro_model->apellido = 'Staff Staff';
			
			$usuario_model->login = '1111111';
			$usuario_model->active = false;
			$usuario_model->type = 'm';
			$usuario_model->administrator = false;
			$usuario_model->email = '';
		
			$maestro_model->create ();
			$usuario_model->create ();
		}
		
		/* Detectar cabeceras */
		$linea = fgetcsv ($archivo, 600, ',', '"');
		
		if ($linea === false || is_null ($linea)) {
			throw new Exception ('No hay cabecera, o es una linea vacia');
		}
		
		$cabecera = Calif_Utils_detectarColumnas ($linea);
		
		if ($this->cleaned_data['maestrosnrc']) {
			/* Crear maestros */
			$this->cleaned_data['maestros'] = true;
		}
		
		if ($this->cleaned_data['nrcs']) {
			/* Agregar NRCs implica agregar Materias */
			$this->cleaned_data['materias'] = true;
		}
		
		if ($this->cleaned_data['horarios']) {
			/* Agregar horarios implica crear salones */
			$this->cleaned_data['salones'] = true;
		}
		
		/* Verificar que existan los campos necesarios */
		if ($this->cleaned_data['maestros']) {
			if (!isset ($cabecera['profesor'])) {
				throw new Exception ('Se solicitó importar profesores, pero el archivo no contiene profesores');
			}
		}
		
		if ($this->cleaned_data['materias']) {
			if (!isset ($cabecera['clave']) || !isset ($cabecera['materia'])) {
				throw new Exception ('Se solicitó importar materias, pero el archivo no contiene claves o materias');
			}
		}
		
		if ($this->cleaned_data['nrcs']) {
			if (!isset ($cabecera['secc']) || !isset ($cabecera['nrc'])) {
				throw new Exception ('Se solicitó importar nrcs, pero el archivo no contiene nrcs o secciones');
			}
		}
		
		if ($this->cleaned_data ['salones']) {
			if (!isset ($cabecera['edif']) || !isset ($cabecera['aula'])) {
				throw new Exception ('Se solicitó importar salones, pero el archivo no contiene edificios o aulas');
			}
		}
		
		if ($this->cleaned_data ['horarios']) {
			if (!isset ($cabecera['ini']) || !isset ($cabecera['fin']) ||
			    !isset ($cabecera['l']) || !isset ($cabecera['m']) ||
			    !isset ($cabecera['i']) || !isset ($cabecera['j']) ||
			    !isset ($cabecera['v']) || !isset ($cabecera['s'])) {
				throw new Exception ('Se solicitó importar horarios para los nrcs, pero el archivo no contiene los campos suficientes (hora de inicio, de fin, lunes, martes, ...)');
			}
		}
		
		$materias = array ();
		$secciones = array ();
		$maestros = array ();
		$salones = array ();
		$edificios = array ();
		
		/* Primera pasada, llenar los arreglos */
		while (($linea = fgetcsv ($archivo, 600, ",", "\"")) !== FALSE) {
			if (is_null ($linea[0])) continue;
			if ($linea[$cabecera['nrc']] === '') {
				throw new Exception ('Alto: NRC vacio. Cerca de la materia '.$linea[$cabecera['clave']].' con sección '.$linea[$cabecera['secc']]);
			}
			
			if ($this->cleaned_data['materias']) {
				Calif_Utils_agregar_materia ($materias, $linea[$cabecera['clave']], $linea[$cabecera['materia']], $linea[$cabecera['departamento']], $linea[$cabecera['cred']]);
			}
			
			if ($this->cleaned_data['maestros']) {
				$codigo_del_maestro = Calif_Utils_agregar_maestro ($maestros, $linea[$cabecera['profesor']]);
			} else {
				$codigo_del_maestro = '1111111';
			}
			
			if ($this->cleaned_data['nrcs'] || $this->cleaned_data['maestrosnrc']) {
				Calif_Utils_agregar_seccion ($secciones, $linea[$cabecera['nrc']], $linea[$cabecera['clave']], $linea[$cabecera['secc']], $codigo_del_maestro);
			}
			if ($this->cleaned_data['salones']) {
				if (isset ($cabecera['cupo'])) {
					$cupo = $linea[$cabecera['cupo']];
				} else {
					$cupo = 0;
				}
				
				if ($linea[$cabecera['edif']] == '') {
					$linea[$cabecera['edif']] = 'DNONE';
				}
				
				if ($linea[$cabecera['aula']] == '') {
					$linea[$cabecera['aula']] = 'A999';
				}
				
				Calif_Utils_agregar_salon ($salones, $linea [$cabecera['edif']], $linea [$cabecera['aula']], $linea[$cabecera['cupo']]);
			}
		}
		
		$departamento = new Calif_Departamento ();
		if (false === ($departamento->get (0))) {
			$departamento->clave = 0;
			$departamento->descripcion = 'Sin departamento';
		}
		
		/* Crear todas las materias */
		if ($this->cleaned_data['materias']) {
			$materia_model = new Calif_Materia ();
			$materia_ram = new Calif_Materia ();
			
			/* Crear un modelo conectado a una tabla ram, para agilizar las inserciones */
			$materia_ram->_a['table'] = 'ram_'.$materia_model->_a['table'];
			$temp_tabla = $materia_ram->getSqlTable ();
			
			$sql = 'CREATE TABLE '.$temp_tabla.' LIKE '.$materia_model->getSqlTable();
			$con->execute ($sql);
			
			$sql = 'ALTER TABLE '.$temp_tabla.' ENGINE=MEMORY';
			$con->execute ($sql);
			
			/* Si la materia no existe en la tabla real, insertarlo en la de ram */
			foreach ($materias as $clave => $data) {
				if ($materia_model->get ($clave) === false) {
					$materia_ram->setFromFormData ($data);
					
					$materia_ram->create ();
				}
			}
			
			/* Copiar todo lo insertado en ram sobre la tabla real */
			$sql = 'INSERT INTO '.$materia_model->getSqlTable ().' SELECT * FROM '.$temp_tabla;
			$con->execute ($sql);
			
			$sql = 'DROP TABLE '.$temp_tabla;
			$con->execute ($sql);
		}
		
		/* Crear todos los maestros */
		if ($this->cleaned_data['maestros']) {
			$maestro_ram = new Calif_Maestro ();
			$usuario_ram = new Calif_User ();
			
			/* Crear temporal de maestros */
			$maestro_ram->_a['table'] = 'ram_'.$maestro_model->_a['table'];
			$temp_tabla = $maestro_ram->getSqlTable ();
			
			$sql = 'CREATE TABLE '.$temp_tabla.' LIKE '.$maestro_model->getSqlTable ();
			$con->execute ($sql);
			
			$sql = 'ALTER TABLE '.$temp_tabla.' ENGINE=MEMORY';
			$con->execute ($sql);
			
			/* Crear temporal de usuarios */
			$usuario_ram->_a['table'] = 'ram_'.$usuario_model->_a['table'];
			$temp_tabla_u = $usuario_ram->getSqlTable ();
			
			$sql = 'CREATE TABLE '.$temp_tabla_u.' LIKE '.$usuario_model->getSqlTable ();
			$con->execute ($sql);
			
			$sql = 'ALTER TABLE '.$temp_tabla_u.' ENGINE=MEMORY';
			$con->execute ($sql);
			
			$maxid = $usuario_model->maxID ();
			$sql = 'ALTER TABLE '.$temp_tabla_u.' AUTO_INCREMENT = '.$maxid;
			$con->execute ($sql);
			
			foreach ($maestros as $codigo => $value) {
				if ($maestro_model->get ($codigo) === false) {
					$maestro_ram->codigo = $codigo;
					$maestro_ram->nombre = $value[0];
					$maestro_ram->apellido = $value[1];
			
					$maestro_ram->create ();
				}
				$sql = new Gatuf_SQL ('login=%s', $codigo);
				
				if ($usuario_model->getList (array ('count' => true, 'filter' => $sql->gen ())) == 0) {
					$usuario_ram->login = $codigo;
					$usuario_ram->password = '12345'; /* Generar aleatoria y enviar por correo */
					$usuario_ram->email = '';
					$usuario_ram->type = 'm';
					$usuario_ram->administrator = false;
					
					$usuario_ram->create ();
				}
			}
			
			/* Copiar y destruir la tabla de maestros */
			$sql = 'INSERT INTO '.$maestro_model->getSqlTable ().' SELECT * FROM '.$temp_tabla;
			$con->execute ($sql);
			
			$sql = 'DROP TABLE '.$temp_tabla;
			$con->execute ($sql);
			
			/* Copiar y destruir la tabla de usuarios */
			$sql = 'INSERT INTO '.$usuario_model->getSqlTable ().' SELECT * FROM '.$temp_tabla_u;
			$con->execute ($sql);
			
			$sql = 'DROP TABLE '.$temp_tabla_u;
			$con->execute ($sql);
		}
		
		$seccion_model = new Calif_Seccion ();
		
		if ($this->cleaned_data['nrcs'] || $this->cleaned_data['maestrosnrc'] || $this->cleaned_data['horarios']) {
			$seccion_ram = new Calif_Seccion ();
			
			$seccion_ram->_a['table'] = 'ram_'.$seccion_model->_a['table'];
			$temp_tabla = $seccion_ram->getSqlTable ();
			
			$sql = 'CREATE TABLE '.$temp_tabla.' LIKE '.$seccion_model->getSqlTable();
			$con->execute ($sql);
			
			$sql = 'ALTER TABLE '.$temp_tabla.' ENGINE=MEMORY';
			$con->execute ($sql);
			
			foreach ($secciones as $nrc => $data) {
				if ($seccion_model->get ($nrc) === false) {
					if ($this->cleaned_data['nrcs']) {
						/* El NRC no existe, crearlo */
						$seccion_ram->setFromFormData ($data);
						
						$seccion_ram->create ();
					} /* Si el nrc no existe, no importa */
				} else {
					if ($this->cleaned_data['horarios'] || $this->cleaned_data['destruirnrcs']) {
						/* Se solicitó destruir el nrc, hay que eliminar todos sus horarios */
						$horas = $seccion_model->get_calif_horario_list ();
						
						foreach ($horas as $hora) {
							$hora->delete ();
						}
					}
					if ($this->cleaned_data['destruirnrcs']) {
						/* Y recrearlo */
						$seccion_model->delete ();
						
						$seccion_ram->setFromFormData ($data);
					
						$seccion_ram->create ();
					} else if ($this->cleaned_data['maestrosnrc']) {
						/* El nrc ya existe, pero hay que actualizar el maestro */
						$seccion_model->setFromFormData (array ('maestro' => $data['maestro']));
						$seccion_model->update ();
					}
				}
			}
			/* Copiar y destruir la tabla de seccion */
			$sql = 'INSERT INTO '.$seccion_model->getSqlTable ().' SELECT * FROM '.$temp_tabla;
			$con->execute ($sql);
			
			$sql = 'DROP TABLE '.$temp_tabla;
			$con->execute ($sql);
		}
		
		if ($this->cleaned_data['salones']) {
			$salon_model = new Calif_Salon ();
			$edificio_model = new Calif_Edificio ();
			ksort ($salones);
			foreach ($salones as $edificio => &$aulas) {
				if (false === $edificio_model->get ($edificio)) {
					/* Si el edificio no existe, también crearlo */
					$edificio_model->clave = $edificio;
					$edificio_model->descripcion = 'Edificio '.$edificio;
					$edificio_model->create ();
				}
				ksort ($aulas);
				/* Y luego crear las aulas */
				foreach ($aulas as $aula => &$cupo) {
					if ($salon_model->getSalon ($edificio, $aula) === false) {
						$salon_model->setFromFormData (array ('edificio' => $edificio, 'aula' => $aula, 'cupo' => $cupo));
				
						$salon_model->create ();
					}
			
					$cupo = $salon_model->id;
				}
			}
		}
		
		if ($this->cleaned_data['horarios']) {
			rewind ($archivo);
			/* Descartar las cabeceras */
			$linea = fgetcsv ($archivo, 600, ',', '"');
			
			$horario_ram = new Calif_Horario ();
			$horario_model = new Calif_Horario ();
			
			$horario_ram->_a['table'] = 'ram_'.$horario_ram->_a['table'];
			$temp_tabla = $horario_ram->getSqlTable ();
			
			$sql = 'CREATE TABLE '.$temp_tabla.' LIKE '.$horario_model->getSqlTable();
			$con->execute ($sql);
			
			$sql = 'ALTER TABLE '.$temp_tabla.' ENGINE=MEMORY';
			$con->execute ($sql);
			
			/* Segunda pasada, crear los horarios */
			while (($linea = fgetcsv ($archivo, 600, ',', '"')) !== FALSE) {
				if (is_null ($linea[0])) continue;
				if ($linea[$cabecera['edif']] == '') {
					$linea[$cabecera['edif']] = 'DNONE';
				}
				
				if ($linea[$cabecera['aula']] == '') {
					$linea[$cabecera['aula']] = 'A999';
				}
				if ($linea[$cabecera['ini']] == '' || $linea[$cabecera['fin']] == '') {
					$linea[$cabecera['ini']] = '300';
					$linea[$cabecera['fin']] = '455';
				}
				
				if ($seccion_model->get ($linea[$cabecera['nrc']]) === false) continue;
				
				$horario_ram->setFromFormData (array ('nrc' => $linea[$cabecera['nrc']],
					'salon' => $salones[$linea[$cabecera['edif']]][$linea[$cabecera['aula']]]));
				$horario_ram->inicio = Calif_Utils_horaFromSiiau ($linea[$cabecera['ini']]);
				$horario_ram->fin = Calif_Utils_horaFromSiiau ($linea[$cabecera['fin']]);
				foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
					$horario_ram->$dia = $linea[$cabecera[$dia]];
				}
				
				$horario_ram->create ();
			}
			
			$sql = 'INSERT INTO '.$horario_model->getSqlTable ().' SELECT * FROM '.$temp_tabla;
			$con->execute ($sql);
			
			$sql = 'DROP TABLE '.$temp_tabla;
			$con->execute ($sql);
		}
		
		fclose ($archivo);
	}
}
