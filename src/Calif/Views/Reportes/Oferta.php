<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Reportes_Oferta {
	public $conCambios_precond = array ('Calif_Precondition::jefeORcoordRequired');
	public function conCambios ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Calif_Form_Calendario_SeleccionarTodos ($request->POST);
			
			if ($form->isValid ()) {
				$cal = $form->save ();
				
				if ($cal->clave != Calif_Calendario_getDefault ()) {
					$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Reportes_Oferta::conCambiosODS', $cal->clave);
					return new Gatuf_HTTP_Response_Redirect ($url);
				} else {
					$request->user->setMessage (3, 'El calendario seleccionado tiene que ser diferente al actual para calcular cambios');
				}
			}
		} else {
			$form = new Calif_Form_Calendario_SeleccionarTodos (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/reportes/oferta/con-cambios.html',
		                                          array ('page_title' => 'Exportar secciones con cambios',
		                                                 'form' => $form),
		                                          $request);
	}
	
	public $conCambiosODS_precond = array ('Calif_Precondition::jefeORcoordRequired');
	public function conCambiosODS ($request, $match) {
		$cal = new Calif_Calendario ();
		
		if (false === ($cal->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		Gatuf::loadFunction ('Calif_Calendario_getDefault');
		if ($cal->clave == Calif_Calendario_getDefault ()) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Recoger los filtros de la oferta */
		$sql = new Gatuf_SQL ();
		
		/* Verificar filtro por Carrera */
		$car = $request->session->getData('filtro_seccion_asignada_carrera', null);
		$div = $request->session->getData('filtro_seccion_asignada_division', null);
		$noasig = $request->session->getData('filtro_seccion_asignada_no', false);
		if ($noasig === true) {
			$filtro['n'] = 'Secciones no solicitadas';
			
			$sql->Q ('asignacion IS NULL');
		} else if (!is_null ($car)) {
			$carrera = new Calif_Carrera ($car);
			$filtro['c'] = 'Secciones asignadas a la carrera "'.$carrera->descripcion.'"';
			
			$sql->Q ('asignacion=%s', $car);
		} else if (!is_null ($div)) {
			$division = new Calif_Division ($div);
			$filtro['i'] = 'Secciones asignadas a la división "'.$division->descripcion.'"';
			$escape = array ();
			$claves = array ();
			$carreras = $division->get_calif_carrera_list ();
			foreach ($carreras as $car) {
				$escape[] = '%s';
				$claves[] = $car->clave;
			}
			
			$sql->Q ('asignacion IN ('.implode (', ', $escape).')', $claves);
		}
		
		/* Verificar filtro de secciones por departamento */
		$dep = $request->session->getData('filtro_seccion_departamento',null);
		if (!is_null ($dep) ){
			$departamento = new Calif_Departamento ();
			$departamento->get ($dep);
			$filtro['d'] = $departamento->descripcion;
			$sql->Q('materia_departamento=%s', $dep);
		}
		
		if (count ($sql->ands) == 0) {
			$secciones_hoy = Gatuf::factory ('Calif_Seccion')->getList (array ('view' => 'paginador'));
		} else {
			$secciones_hoy = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen (), 'view' => 'paginador'));
		}
		
		$libro_ods = new Gatuf_ODS ();
		
		$libro_ods->addNewSheet ('Principal');
		$libro_ods->addStringCell ('Principal', 1, 1, 'NRC');
		$libro_ods->addStringCell ('Principal', 1, 2, 'Clave de la materia');
		$libro_ods->addStringCell ('Principal', 1, 3, 'Descripcion');
		$libro_ods->addStringCell ('Principal', 1, 4, 'Seccion');
		$libro_ods->addStringCell ('Principal', 1, 5, 'Clave departamento');
		$libro_ods->addStringCell ('Principal', 1, 6, 'Departamento');
		$libro_ods->addStringCell ('Principal', 1, 7, 'Apellido del profesor');
		$libro_ods->addStringCell ('Principal', 1, 8, 'Nombre del profesor');
		$libro_ods->addStringCell ('Principal', 1, 9, 'Codigo del profesor');
		$libro_ods->addStringCell ('Principal', 1, 10, 'Inicia a las');
		$libro_ods->addStringCell ('Principal', 1, 11, 'Termina a las');
		$libro_ods->addStringCell ('Principal', 1, 12, 'Edificio');
		$libro_ods->addStringCell ('Principal', 1, 13, 'Aula');
		$libro_ods->addStringCell ('Principal', 1, 14, 'L');
		$libro_ods->addStringCell ('Principal', 1, 15, 'M');
		$libro_ods->addStringCell ('Principal', 1, 16, 'I');
		$libro_ods->addStringCell ('Principal', 1, 17, 'J');
		$libro_ods->addStringCell ('Principal', 1, 18, 'V');
		$libro_ods->addStringCell ('Principal', 1, 19, 'S');
		$libro_ods->addStringCell ('Principal', 1, 20, 'Carrera');
		$libro_ods->addStringCell ('Principal', 1, 21, 'Cambio de horario');
		$libro_ods->addStringCell ('Principal', 1, 22, 'Cambio de aula');
		$libro_ods->addStringCell ('Principal', 1, 23, 'Nueva');
		
		$dias = array ('l', 'm', 'i', 'j', 'v', 's');
		$g = 2;
		
		$seccion_ayer = new Calif_Seccion ();
		$seccion_ayer->_a['calpfx'] = $cal->clave;
		
		$horas_ayer_model = new Calif_Horario ();
		$horas_ayer_model->setCalpfx ($cal->clave);
		$sql_horas = new Gatuf_SQL ();
		
		foreach ($secciones_hoy as $seccion_actual) {
			$horas_hoy = $seccion_actual->get_calif_horario_list (array ('view' => 'paginador', 'order' => 'l ASC, m ASC, i ASC, j ASC, v ASC, s ASC'));
			$cant_horas = count ($horas_hoy);
			$departamento = new Calif_Departamento ($seccion_actual->materia_departamento);
			
			$nueva = false;
			$horario_movido = false;
			$aula_movida = false;
			
			if ($seccion_ayer->get ($seccion_actual->nrc) === false) {
				$nueva = true;
			} else {
				$sql_horas->ands = array ();
				$sql_horas->Q ('nrc=%s', $seccion_actual->nrc);
				$horas_ayer = $horas_ayer_model->getList (array ('view' => 'paginador', 'filter' => $sql_horas->gen (), 'order' => 'l ASC, m ASC, i ASC, j ASC, v ASC, s ASC'));
				
				if (count ($horas_ayer) != $cant_horas) {
					$horario_movido = true;
					$aula_movida = true;
				} else {
					/* Comparar cada hora contra cada hora */
					foreach ($horas_hoy as $index => $una_hora_hoy) {
						$una_hora_ayer = $horas_ayer[$index];
						
						if ($una_hora_hoy->l != $una_hora_ayer->l ||
						    $una_hora_hoy->m != $una_hora_ayer->m ||
						    $una_hora_hoy->i != $una_hora_ayer->i ||
						    $una_hora_hoy->j != $una_hora_ayer->j ||
						    $una_hora_hoy->v != $una_hora_ayer->v ||
						    $una_hora_hoy->s != $una_hora_ayer->s ||
						    $una_hora_hoy->inicio != $una_hora_ayer->inicio ||
						    $una_hora_hoy->fin != $una_hora_ayer->fin) {
							$horario_movido = true;
						}
						
						if ($una_hora_hoy->salon_aula != $una_hora_ayer->salon_aula ||
						    $una_hora_hoy->salon_edificio != $una_hora_ayer->salon_edificio) {
							$aula_movida = true;
						}
					}
				}
			}
			
			if ($cant_horas == 0) {
				/* Imprimir, pero sin horas */
				$libro_ods->addStringCell ('Principal', $g, 1, $seccion_actual->nrc);
				$libro_ods->addStringCell ('Principal', $g, 2, $seccion_actual->materia);
				$libro_ods->addStringCell ('Principal', $g, 3, $seccion_actual->materia_desc);
				$libro_ods->addStringCell ('Principal', $g, 4, $seccion_actual->seccion);
				$libro_ods->addStringCell ('Principal', $g, 5, $seccion_actual->materia_departamento);
				$libro_ods->addStringCell ('Principal', $g, 6, $departamento->descripcion);
				$libro_ods->addStringCell ('Principal', $g, 7, $seccion_actual->maestro_apellido);
				$libro_ods->addStringCell ('Principal', $g, 8, $seccion_actual->maestro_nombre);
				$libro_ods->addStringCell ('Principal', $g, 9, $seccion_actual->maestro);
				
				for ($h = 10; $h < 20; $h++) {
					$libro_ods->addStringCell ('Principal', $g, $h, 'N/A');
				}
				
				$libro_ods->addStringCell ('Principal', $g, 20, $seccion_actual->asignacion);
				if ($nueva) {
					$libro_ods->addStringCell ('Principal', $g, 23, '** NUEVA **');
				} else if ($horario_movido) {
					$libro_ods->addStringCell ('Principal', $g, 21, '** Horario movido **');
					$libro_ods->addStringCell ('Principal', $g, 22, '** Horario movido **');
				} else if ($aula_movida) {
					$libro_ods->addStringCell ('Principal', $g, 22, '** Aula movida **');
				}
				$g++;
			} else {
				foreach ($horas_hoy as $hora) {
					/* Recuperar sus horas, e imprimirlas */
					$libro_ods->addStringCell ('Principal', $g, 1, $seccion_actual->nrc);
					$libro_ods->addStringCell ('Principal', $g, 2, $seccion_actual->materia);
					$libro_ods->addStringCell ('Principal', $g, 3, $seccion_actual->materia_desc);
					$libro_ods->addStringCell ('Principal', $g, 4, $seccion_actual->seccion);
					$libro_ods->addStringCell ('Principal', $g, 5, $seccion_actual->materia_departamento);
					$libro_ods->addStringCell ('Principal', $g, 6, $departamento->descripcion);
					$libro_ods->addStringCell ('Principal', $g, 7, $seccion_actual->maestro_apellido);
					$libro_ods->addStringCell ('Principal', $g, 8, $seccion_actual->maestro_nombre);
					$libro_ods->addStringCell ('Principal', $g, 9, $seccion_actual->maestro);
					$libro_ods->addStringCell ('Principal', $g, 10, $hora->inicio);
					$libro_ods->addStringCell ('Principal', $g, 11, $hora->fin);
					$libro_ods->addStringCell ('Principal', $g, 12, $hora->salon_edificio);
					$libro_ods->addStringCell ('Principal', $g, 13, $hora->salon_aula);
					
					$h = 14;
					foreach ($dias as $dia) {
						$libro_ods->addStringCell ('Principal', $g, $h, $hora->$dia);
						$h++;
					}
					$libro_ods->addStringCell ('Principal', $g, 20, $seccion_actual->asignacion);
					
					if ($nueva) {
						$libro_ods->addStringCell ('Principal', $g, 23, '** NUEVA **');
					} else if ($horario_movido) {
						$libro_ods->addStringCell ('Principal', $g, 21, '** Horario movido **');
						$libro_ods->addStringCell ('Principal', $g, 22, '** Horario movido **');
					} else if ($aula_movida) {
						$libro_ods->addStringCell ('Principal', $g, 22, '** Aula movida **');
					}
					$g++;
				}
			}
		}
		
		$libro_ods->construir_paquete ();
		
		return new Gatuf_HTTP_Response_File ($libro_ods->nombre, 'Oferta-filtrado.ods', 'application/vnd.oasis.opendocument.spreadsheet', true);
	}
}
