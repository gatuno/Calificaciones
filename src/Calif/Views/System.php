<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Calif_Views_System {
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('calif/system/index.html',
		                                  array ('page_title' => 'Sistema y utilerías'),
		                                  $request);
	}
	
	public $importarNrcInventado_precond = array ('Gatuf_Precondition::adminRequired');
	public function importarNrcInventado ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Calif_Form_System_SubirArchivo (array_merge ($request->POST, $request->FILES));
			
			if ($form->isValid ()) {
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_System::importarNrcInventado');
				$ruta = $form->save ();
				
				if (($archivo = fopen ($ruta, "r")) === false) {
					$request->user->setMessage (3, 'Hubo un error con la subida de archivos');
					return new Gatuf_HTTP_Redirect ($url);
				}
				
				/* Detectar cabeceras */
				$linea = fgetcsv ($archivo, 600, ',', '"');
				
				if ($linea === false || is_null ($linea)) {
					$request->user->setMessage (3, 'No hay cabecera, o es una linea vacia');
					return new Gatuf_HTTP_Redirect ($url);
				}
				
				$cabecera = Calif_Utils_detectarColumnas ($linea);
				
				if (!isset ($cabecera['nrc'])) {
					/* Se requiere una columna de NRC */
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de nrc');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['secc'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de sección');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['clave'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de materia');
					return new Gatuf_HTTP_Redirect ($url);
				}
				$secciones = array ();
				
				while (($linea = fgetcsv ($archivo, 600, ",", "\"")) !== FALSE) {
					if (is_null ($linea[0])) continue;
					$nrc = $linea[$cabecera['nrc']];
					$clave = $linea[$cabecera['clave']];
					$seccion = $linea[$cabecera['secc']];
					
					if ($nrc === '') {
						$request->user->setMessage (3, 'Nrc vacio');
						return new Gatuf_HTTP_Redirect ($url);
					}
					
					if (!isset ($secciones[$clave])) {
						$secciones[$clave] = array ();
					}
					
					$secciones[$clave][$seccion] = $nrc;
				}
				
				fclose ($archivo);
				
				/* Empezar a buscar todos los nrcs inventados */
				$inventados = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => 'nrc >= 80000'));
				$log_cambiados = array ();
				
				foreach ($inventados as $inventado) {
					if (isset ($secciones[$inventado->materia][$inventado->seccion])) {
						$log_cambiados[] = sprintf ('Cambiando NRC %s, materia %s, sección %s al nuevo NRC %s', $inventado->nrc, $inventado->materia, $inventado->seccion, $secciones[$inventado->materia][$inventado->seccion]);
						$inventado->cambiaNrc ($secciones[$inventado->materia][$inventado->seccion]);
					}
				}
				
				unset ($secciones);
				
				$inventados = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => 'nrc >= 80000'));
				return Gatuf_Shortcuts_RenderToResponse ('calif/system/reporte_nrc_inventado.html',
				                                          array ('page_title' => 'Reporte de nrc inventados',
				                                                 'inventados' => $inventados,
				                                                 'cambiados' => $log_cambiados),
				                                          $request);
			}
		} else {
			$form = new Calif_Form_System_SubirArchivo (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/system/importa_nrc_inventado.html',
		                                          array ('page_title' => 'Importar nrc inventados',
		                                                 'form' => $form),
		                                          $request);
	}
	
	public $auditoriaSevera_precond = array ('Gatuf_Precondition::adminRequired');
	public function auditoriaSevera ($request, $match, $params) {
		if ($request->method == 'POST') {
			if ($params['car'] == false) {
				$form = new Calif_Form_System_AuditoriaSevera (array_merge ($request->POST, $request->FILES));
			} else {
				$form = new Calif_Form_System_AuditoriaSeveraCarrera (array_merge ($request->POST, $request->FILES));
			}
			
			if ($form->isValid ()) {
				$data = $form->save ();
				
				/* Intentar abrir y procesar el archivo */
				if ($params['car'] == false) {
					$url = Gatuf_HTTP_URL_urlForView ('auditoriaSeveraDepartamento');
				} else {
					$url = Gatuf_HTTP_URL_urlForView ('auditoriaSeveraCarrera');
				}
				
				if (($archivo = fopen ($data['archivo'], "r")) === false) {
					$request->user->setMessage (3, 'Hubo un error con la subida de archivos');
					return new Gatuf_HTTP_Redirect ($url);
				}
				
				/* Detectar cabeceras */
				$linea = fgetcsv ($archivo, 600, ',', '"');
				
				if ($linea === false || is_null ($linea)) {
					$request->user->setMessage (3, 'No hay cabecera, o es una linea vacia');
					return new Gatuf_HTTP_Redirect ($url);
				}
				
				$cabecera = Calif_Utils_detectarColumnas ($linea);
				
				if (!isset ($cabecera['nrc'])) {
					/* Se requiere una columna de NRC */
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de nrc');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['secc'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de sección');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['clave'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de materia');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['departamento'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de departamento');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['profesor'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de profesor');
					return new Gatuf_HTTP_Redirect ($url);
				}
				
				$con = &Gatuf::db();
				
				$seccion_siiau = new Calif_Seccion ();
				$seccion_siiau->_a['table'] = 'ram_'.$seccion_siiau->_a['table'];
				$temp_tabla = $seccion_siiau->getSqlTable ();
				
				$sql = 'DROP TABLE IF EXISTS '.$temp_tabla;
				$con->execute ($sql);
				
				$sql = 'CREATE TABLE '.$temp_tabla.' LIKE '.Gatuf::factory('Calif_Seccion')->getSqlTable ();
				$con->execute ($sql);
				
				$sql = 'ALTER TABLE '.$temp_tabla.' ENGINE=MEMORY';
				$con->execute ($sql);
				
				/* Primera pasada, llenar las tablas */
				while (($linea = fgetcsv ($archivo, 600, ",", "\"")) !== FALSE) {
					if (is_null ($linea[0])) continue;
					if ($linea[$cabecera['nrc']] === '') {
						$request->user->setMessage (3, 'Nrc vacio');
						return new Gatuf_HTTP_Redirect ($url);
					}
					
					$depa_num = Calif_Utils_map_departamento ($linea[$cabecera['departamento']]);
					if ($params['car'] == false && $data['dep'] != -1 && $depa_num != $data['dep']) continue;
					
					$nrc = $linea[$cabecera['nrc']];
					$materia = $linea[$cabecera['clave']];
					$seccion = $linea[$cabecera['secc']];
					$profesor = $linea[$cabecera['profesor']];
					$profesor = trim (strstr ($profesor, '('), '() ');
					if ($profesor == '') $profesor = '2222222';
					
					if ($seccion_siiau->get ($nrc) === false) {
						$seccion_siiau->setFromFormData (array ('nrc' => $nrc, 'materia' => $materia, 'seccion' => $seccion, 'maestro' => $profesor));
						$seccion_siiau->create (true);
					}
				}
				
				fclose ($archivo);
				/* Ya está procesado todo, ahora hacer la auditoria */
				$sql = new Gatuf_SQL();
				if ($params['car'] == false && $data['dep'] != -1) {
					$sql->Q ('materia_departamento=%s', $data['dep']);
				} else if ($params['car'] == true) {
					$sql->Q ('asignacion=%s', $data['car']);
				}
				
				$no_en_siiau = array ();
				$no_en_sistema = array ();
				$errores = array ();
				$filter = $sql->gen ();
				if ($filter == '') $filter = null;
				$secciones = Gatuf::factory ('Calif_Seccion')->getList (array ('view' => 'paginador', 'filter' => $filter));
				
				foreach ($secciones as $a_auditar) {
					if ($seccion_siiau->get ($a_auditar->nrc) === false) {
						/* No existe en siiau */
						$no_en_siiau[] = $a_auditar;
						continue;
					}
					
					if ($seccion_siiau->materia != $a_auditar->materia || $seccion_siiau->seccion != $a_auditar->seccion || $seccion_siiau->maestro != $a_auditar->maestro) {
						$errores[] = array ($a_auditar, clone ($seccion_siiau));
					}
				}
				
				if ($params['car'] == false) {
					$seccion_model = new Calif_Seccion ();
					foreach ($seccion_siiau->getList (array ('order' => 'materia ASC, seccion ASC')) as $a_auditar) {
						if ($seccion_model->get ($a_auditar->nrc) === false) {
							$no_en_sistema [] = $a_auditar;
						}
					}
				}
				
				$sql = 'DROP TABLE IF EXISTS '.$seccion_siiau->getSqlTable ();
				$con->execute ($sql);
				
				return Gatuf_Shortcuts_RenderToResponse ('calif/system/reporte_auditoria_severa.html',
				                                          array ('page_title' => 'Reporte de Auditoria Severa',
				                                                 'no_en_siiau' => $no_en_siiau,
				                                                 'no_en_sistema' => $no_en_sistema,
				                                                 'errores' => $errores),
				                                          $request);
			}
		} else {
			if ($params['car'] == false) {
				$form = new Calif_Form_System_AuditoriaSevera (null);
			} else {
				$form = new Calif_Form_System_AuditoriaSeveraCarrera (null);
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/system/auditoria_severa.html',
		                                          array ('page_title' => 'Auditoria Severa',
		                                                 'form' => $form),
		                                          $request);
	}
	
	public $auditoriaHoraria_precond = array ('Gatuf_Precondition::adminRequired');
	public function auditoriaHoraria ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Calif_Form_System_AuditoriaHoraria (array_merge ($request->POST, $request->FILES));
			
			if ($form->isValid ()) {
				$data = $form->save ();
				
				/* Intentar abrir y procesar el archivo */
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_System::auditoriaHoraria');
				
				if (($archivo = fopen ($data['archivo'], "r")) === false) {
					$request->user->setMessage (3, 'Hubo un error con la subida de archivos');
					return new Gatuf_HTTP_Redirect ($url);
				}
				
				/* Detectar cabeceras */
				$linea = fgetcsv ($archivo, 600, ',', '"');
				
				if ($linea === false || is_null ($linea)) {
					$request->user->setMessage (3, 'No hay cabecera, o es una linea vacia');
					return new Gatuf_HTTP_Redirect ($url);
				}
				
				$cabecera = Calif_Utils_detectarColumnas ($linea);
				
				if (!isset ($cabecera['nrc'])) {
					/* Se requiere una columna de NRC */
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de nrc');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['secc'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de sección');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['clave'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de materia');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['departamento'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de departamento');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['profesor'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene una columna de profesor');
					return new Gatuf_HTTP_Redirect ($url);
				}
				if (!isset ($cabecera['ini']) ||
				    !isset ($cabecera['fin']) ||
				    !isset ($cabecera['l']) ||
				    !isset ($cabecera['m']) ||
				    !isset ($cabecera['i']) ||
				    !isset ($cabecera['j']) ||
				    !isset ($cabecera['v']) ||
				    !isset ($cabecera['s']) ||
				    !isset ($cabecera['edif']) ||
				    !isset ($cabecera['aula'])) {
					$request->user->setMessage (3, 'El archivo importado no contiene alguna de las columnas necesarias para definir sus horarios');
					return new Gatuf_HTTP_Redirect ($url);
				}
				
				$con = &Gatuf::db();
				
				$seccion_siiau = new Calif_Seccion ();
				$seccion_siiau->_a['table'] = 'ram_'.$seccion_siiau->_a['table'];
				$temp_tabla = $seccion_siiau->getSqlTable ();
				
				$sql = 'DROP TABLE IF EXISTS '.$temp_tabla;
				$con->execute ($sql);
				
				$sql = 'CREATE TABLE '.$temp_tabla.' LIKE '.Gatuf::factory('Calif_Seccion')->getSqlTable ();
				$con->execute ($sql);
				
				$sql = 'ALTER TABLE '.$temp_tabla.' ENGINE=MEMORY';
				$con->execute ($sql);
				
				$horario_siiau = new Calif_Horario ();
				$horario_siiau->_a['table'] = 'ram_'.$horario_siiau->_a['table'];
				$temp_tabla = $horario_siiau->getSqlTable ();
				
				$sql = 'DROP TABLE IF EXISTS '.$temp_tabla;
				$con->execute ($sql);
				
				$sql = 'CREATE TABLE '.$temp_tabla.' LIKE '.Gatuf::factory('Calif_Horario')->getSqlTable ();
				$con->execute ($sql);
				
				$sql = 'ALTER TABLE '.$temp_tabla.' ENGINE=MEMORY';
				$con->execute ($sql);
				
				$salon_model = new Calif_Salon ();
				$edificio_model = new Calif_Edificio ();
				
				/* Primera pasada, llenar las tablas */
				while (($linea = fgetcsv ($archivo, 600, ",", "\"")) !== FALSE) {
					if (is_null ($linea[0])) continue;
					if ($linea[$cabecera['nrc']] === '') {
						$request->user->setMessage (3, 'Nrc vacio');
						return new Gatuf_HTTP_Redirect ($url);
					}
					
					$depa_num = Calif_Utils_map_departamento ($linea[$cabecera['departamento']]);
					if ($data['dep'] != -1 && $depa_num != $data['dep']) continue;
					
					$nrc = $linea[$cabecera['nrc']];
					$materia = $linea[$cabecera['clave']];
					$seccion = $linea[$cabecera['secc']];
					$profesor = $linea[$cabecera['profesor']];
					$profesor = trim (strstr ($profesor, '('), '() ');
					if ($profesor == '') $profesor = '2222222';
					
					if ($seccion_siiau->get ($nrc) === false) {
						$seccion_siiau->setFromFormData (array ('nrc' => $nrc, 'materia' => $materia, 'seccion' => $seccion, 'maestro' => $profesor));
						$seccion_siiau->create (true);
					}
					if ($linea[$cabecera['edif']] == '' && $linea[$cabecera['aula']] == '' && $linea[$cabecera['ini']] == '' && $linea[$cabecera['fin']] == '') continue;
					$edificio = $linea[$cabecera['edif']];
					if ($edificio == '') $edificio = 'DNONE';
					$aula = $linea[$cabecera['aula']];
					if ($aula == '') $aula = 'A999';
					$hora_inicio = $linea[$cabecera['ini']];
					if ($hora_inicio == '') $hora_inicio = '300';
					$hora_fin = $linea[$cabecera['fin']];
					if ($hora_fin == '') $hora_fin = '455';
					
					if ($salon_model->getSalon ($edificio, $aula) === false) {
						if ($edificio_model->get ($edificio) === false) {
							$edificio_model->clave = $edificio;
							$edificio_model->descripcion = 'Edificio '.$edificio;
							$edificio_model->create ();
						}
						
						$salon_model->setFromFormData (array ('edificio' => $edificio, 'aula' => $aula, 'cupo' => 24));
						$salon_model->create ();
					}
					
					foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
						$horario_siiau->$dia = $linea[$cabecera[$dia]];
					}
					$horario_siiau->setFromFormData (array ('nrc' => $seccion_siiau->nrc, 'inicio' => Calif_Utils_horaFromSiiau ($hora_inicio), 'fin' => Calif_Utils_horaFromSiiau ($hora_fin), 'salon' => $salon_model->id));
					
					$horario_siiau->create ();
				}
				
				fclose ($archivo);
				/* Ya está procesado todo, ahora hacer la auditoria */
				$sql = new Gatuf_SQL('asignacion=%s', $data['car']);
				if ($data['dep'] != -1) {
					$sql->Q ('materia_departamento=%s', $data['dep']);
				}
				
				$secciones = Gatuf::factory ('Calif_Seccion')->getList (array ('view' => 'paginador', 'filter' => $sql->gen(), 'order' => 'materia ASC, seccion ASC'));
				
				$errores = array ();
				
				foreach ($secciones as $a_auditar) {
					if ($seccion_siiau->get ($a_auditar->nrc) === false) continue; /* Omitir, no existe en siiau */
					
					$horas_local = $a_auditar->get_calif_horario_list ();
					$sql = new Gatuf_SQL ('nrc=%s', $a_auditar->nrc);
					$horas_siiau = $horario_siiau->getList (array ('filter' => $sql->gen ()));
					
					$hash_local = array ();
					$hash_siiau = array ();
					
					foreach ($horas_local as $hora) {
						$hash_local[] = $hora->hash ();
					}
					foreach ($horas_siiau as $hora) {
						$hash_siiau[] = $hora->hash ();
					}
					sort ($hash_siiau);
					sort ($hash_local);
					if ($hash_local != $hash_siiau) {
						$errores [] = array ($a_auditar, (array) $horas_local, (array) $horas_siiau);
					}
				}
				
				$sql = 'DROP TABLE IF EXISTS '.$seccion_siiau->getSqlTable ();
				$con->execute ($sql);
				
				$sql = 'DROP TABLE IF EXISTS '.$horario_siiau->getSqlTable ();
				$con->execute ($sql);
				
				return Gatuf_Shortcuts_RenderToResponse ('calif/system/reporte_auditoria_horaria.html',
				                                          array ('page_title' => 'Reporte de Auditoria Horaria',
				                                                 'errores' => $errores,
				                                                 ),
				                                          $request);
			}
		} else {
			$form = new Calif_Form_System_AuditoriaHoraria (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/system/auditoria_horaria.html',
		                                          array ('page_title' => 'Auditoria Horaria',
		                                                 'form' => $form),
		                                          $request);
	}
}
