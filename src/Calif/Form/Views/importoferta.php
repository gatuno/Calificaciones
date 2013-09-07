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
			array ('label' => 'Importar maestros',
				'help_text' => 'Si se deben importar los nombres y códigos de los maestros.',
				'required' => true,
				'initial' => true,
				'widget' => 'Gatuf_Form_Widget_CheckboxInput'
		));
		
		$this->fields['maestrosnrc'] = new Gatuf_Form_Field_Boolean (
			array ('label' => 'Importar maestros asignados a NRC',
				'help_text' => 'Se asignarán maestros a los NRC ya existentes. No se crearán NRCs a menos que active la casilla de crear NRCs. En caso contrario se asignará Staff a los maestros. Implica importar los maestros.',
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
		
		$req = sprintf ('TRUNCATE TABLE Calificaciones'); /* FIXME: prefijo de la tabla */
		$con->execute ($req);
		
		$seccion_model = new Calif_Seccion ();
		$req = sprintf ('TRUNCATE TABLE Grupos'); /* FIXME: prefijo de la tabla */
		$con->execute ($req);
	
		$maestro = new Calif_Maestro ();
		
		/* Asegurarnos que exista nuestro amigo Staff */
		if (false === $maestro->getMaestro ('1111111')) {
			$maestro->codigo = '1111111';
			$maestro->nombre = 'Staff';
			$maestro->apellido = 'Staff Staff';
			$maestro->correo = '';
		
			$maestro->create ();
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
				throw new Exception ('Se solicitó importar maestros, pero el archivo no contiene profesores');
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
				Calif_Utils_agregar_materia ($materias, $linea[$cabecera['clave']], $linea[$cabecera['materia']]);
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
		
		if ($this->cleaned_data['materias']) {
			$materia_model = new Calif_Materia ();
			foreach ($materias as $clave => $descripcion) {
				if ($materia_model->getMateria ($clave) === false) {
					$materia_model->clave = $clave;
					$materia_model->descripcion = $descripcion;
					$materia_model->departamento = 0;
					
					$materia_model->create ();
				}
			}
		}
		
		if ($this->cleaned_data['maestros']) {
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
		}
		
		$seccion_model = new Calif_Seccion ();
		
		if ($this->cleaned_data['nrcs'] || $this->cleaned_data['maestrosnrc'] || $this->cleaned_data['horarios']) {
			foreach ($secciones as $nrc => $value) {
				if ($seccion_model->getNrc ($nrc) === false) {
					if ($this->cleaned_data['nrcs']) {
						/* El NRC no existe, crearlo */
						$seccion_model->nrc = $nrc;
						$seccion_model->materia = $value[0];
						$seccion_model->seccion = $value[1];
						$seccion_model->maestro = $value[2];
					
						$seccion_model->create ();
					} /* Si el nrc no existe, no importa */
				} else {
					if ($this->cleaned_data['horarios'] || $this->cleaned_data['destruirnrcs']) {
						/* Se solicitó destruir el nrc, hay que eliminar todos sus horarios */
						$sql = new Gatuf_SQL ('nrc=%s', $seccion_model->nrc);
						$horas = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen()));
				
						foreach ($horas as $hora) {
							$hora->delete ();
						}
					}
					if ($this->cleaned_data['destruirnrcs']) {
						/* Y recrearlo */
						$seccion_model->delete ();
						
						$seccion_model->nrc = $nrc;
						$seccion_model->materia = $value[0];
						$seccion_model->seccion = $value[1];
						$seccion_model->maestro = $value[2];
					
						$seccion_model->create ();
					} else if ($this->cleaned_data['maestrosnrc']) {
						/* El nrc ya existe, pero hay que actualizar el maestro */
						$seccion_model->maestro = $value[2];
						$seccion_model->update ();
					}
				}
			}
		}
		
		if ($this->cleaned_data['salones']) {
			$salon_model = new Calif_Salon ();
			$edificio_model = new Calif_Edificio ();
			ksort ($salones);
			foreach ($salones as $edificio => &$aulas) {
				if (false === $edificio_model->getEdificio ($edificio)) {
					/* Si el edificio no existe, también crearlo */
					$edificio_model->clave = $edificio;
					$edificio_model->descripcion = 'Edificio '.$edificio;
					$edificio_model->create ();
				}
				ksort ($aulas);
				/* Y luego crear las aulas */
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
		}
		
		if ($this->cleaned_data['horarios']) {
			rewind ($archivo);
			/* Descartar las cabeceras */
			$linea = fgetcsv ($archivo, 600, ',', '"');
			
			$horario_model = new Calif_Horario ();
			
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
				
				if ($seccion_model->getNrc ($linea[$cabecera['nrc']]) === false) continue;
				
				$horario_model->nrc = $linea[$cabecera['nrc']];
				$horario_model->hora_inicio = $linea[$cabecera['ini']];
				$horario_model->hora_fin = $linea[$cabecera['fin']];
				$horario_model->salon = $salones[$linea[$cabecera['edif']]][$linea[$cabecera['aula']]];
				$horario_model->lunes = $linea[$cabecera['l']];
				$horario_model->martes = $linea[$cabecera['m']];
				$horario_model->miercoles = $linea[$cabecera['i']];
				$horario_model->jueves = $linea[$cabecera['j']];
				$horario_model->viernes = $linea[$cabecera['v']];
				$horario_model->sabado = $linea[$cabecera['s']];
				/* FIXME: Insertar en una tabla RAM para ganar velocidad */
				$horario_model->create ();
			}
		}
		
		fclose ($archivo);
	}
}
