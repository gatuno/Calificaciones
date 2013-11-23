<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Reportes_Oferta {
	public $porCarrera_precond = array ('Calif_Precondition::coordinadorRequired');
	public function porCarrera ($request, $match) {
		$carrera = new Calif_Carrera ();
		
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->administrator) {
			$coordinadas = $request->user->returnCoord ();
			
			$found = false;
			
			foreach ($coordinadas as $coordinada) {
				if (substr ($coordinada, 18) == $carrera->clave) {
					$found = true;
				}
			}
			
			if (!$found) {
				throw new Gatuf_HTTP_Response_Forbidden ();
			}
		}
		
		/* Generar el reporte */
		/* Las estadisticas primero */
		$sql = new Gatuf_SQL ('asignacion=%s', $carrera->clave);
		$total_secciones = Gatuf::factory ('Calif_Seccion')->getList (array ('count' => true, 'filter' => $sql->gen ()));
		
		$pag = new Gatuf_Paginator (Gatuf::factory ('Calif_Seccion'));
		$pag->model_view = 'paginador';
		$pag->forced_where = $sql;
		
		$pag->action = array ('Calif_Views_Reportes_Oferta::porCarrera', $carrera->clave);
		$pag->summary = sprintf ('Lista de secciones para la carrera "%s"', $carrera->descripcion);
		$list_display = array (
			array ('nrc', 'Gatuf_Paginator_FKLink', 'NRC'),
			array ('materia', 'Gatuf_Paginator_FKLink', 'Materia'),
			array ('seccion', 'Gatuf_Paginator_FKLink', 'Sección'),
			array ('maestro_apellido', 'Gatuf_Paginator_FKLink', 'Maestro'),
		);
		
		$pag->items_per_page = 30;
		$pag->no_results_text = 'No se solicitaron secciones';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('nrc', 'materia', 'seccion', 'materia_desc', 'maestro_nombre', 'maestro_apellido'),
			array ('nrc', 'materia', 'seccion', 'maestro_apellido')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/reportes/oferta/por-carrera.html',
		                                          array ('paginador' => $pag,
		                                                 'carrera' => $carrera,
		                                                 'total' => $total_secciones,
		                                                 'page_title' => 'Secciones para '.$carrera->clave),
		                                          $request);
	}
	
	public $descargaCarreraODS_precond = array ('Calif_Precondition::coordinadorRequired');
	public function descargaCarreraODS ($request, $match) {
		$carrera = new Calif_Carrera ();
		
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->administrator) {
			$coordinadas = $request->user->returnCoord ();
			
			$found = false;
			
			foreach ($coordinadas as $coordinada) {
				if (substr ($coordinada, 18) == $carrera->clave) {
					$found = true;
				}
			}
			
			if (!$found) {
				throw new Gatuf_HTTP_Response_Forbidden ();
			}
		}
		
		$libro_ods = new Gatuf_ODS ();
		
		$libro_ods->addNewSheet ('Principal');
		$libro_ods->addStringCell ('Principal', 1, 1, 'Clave de la materia');
		$libro_ods->addStringCell ('Principal', 1, 2, 'Descripcion');
		$libro_ods->addStringCell ('Principal', 1, 3, 'Seccion');
		$libro_ods->addStringCell ('Principal', 1, 4, 'Clave departamento');
		$libro_ods->addStringCell ('Principal', 1, 5, 'Departamento');
		$libro_ods->addStringCell ('Principal', 1, 6, 'Apellido del profesor');
		$libro_ods->addStringCell ('Principal', 1, 7, 'Nombre del profesor');
		$libro_ods->addStringCell ('Principal', 1, 8, 'Codigo del profesor');
		$libro_ods->addStringCell ('Principal', 1, 9, 'Inicia a las');
		$libro_ods->addStringCell ('Principal', 1, 10, 'Termina a las');
		$libro_ods->addStringCell ('Principal', 1, 11, 'Edificio');
		$libro_ods->addStringCell ('Principal', 1, 12, 'Aula');
		$libro_ods->addStringCell ('Principal', 1, 13, 'L');
		$libro_ods->addStringCell ('Principal', 1, 14, 'M');
		$libro_ods->addStringCell ('Principal', 1, 15, 'I');
		$libro_ods->addStringCell ('Principal', 1, 16, 'J');
		$libro_ods->addStringCell ('Principal', 1, 17, 'V');
		$libro_ods->addStringCell ('Principal', 1, 18, 'S');
		
		$sql = new Gatuf_SQL ('asignacion=%s', $carrera->clave);
		$secciones = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen (), 'view' => 'paginador'));
		
		$dias = array ('l', 'm', 'i', 'j', 'v', 's');
		$g = 2;
		foreach ($secciones as $seccion) {
			$horas = $seccion->get_calif_horario_list (array ('view' => 'paginador'));
			$cant_horas = count ($horas);
			$departamento = new Calif_Departamento ($seccion->materia_departamento);
			
			if ($cant_horas == 0) {
				/* Imprimir, pero sin horas */
				$libro_ods->addStringCell ('Principal', $g, 1, $seccion->materia);
				$libro_ods->addStringCell ('Principal', $g, 2, $seccion->materia_desc);
				$libro_ods->addStringCell ('Principal', $g, 3, $seccion->seccion);
				$libro_ods->addStringCell ('Principal', $g, 4, $seccion->materia_departamento);
				$libro_ods->addStringCell ('Principal', $g, 5, $departamento->descripcion);
				$libro_ods->addStringCell ('Principal', $g, 6, $seccion->maestro_apellido);
				$libro_ods->addStringCell ('Principal', $g, 7, $seccion->maestro_nombre);
				$libro_ods->addStringCell ('Principal', $g, 8, $seccion->maestro);
				
				for ($h = 9; $h < 19; $h++) {
					$libro_ods->addStringCell ('Principal', $g, $h, 'N/A');
				}
				$g++;
			} else {
				foreach ($horas as $hora) {
					/* Recuperar sus horas, e imprimirlas */
					$libro_ods->addStringCell ('Principal', $g, 1, $seccion->materia);
					$libro_ods->addStringCell ('Principal', $g, 2, $seccion->materia_desc);
					$libro_ods->addStringCell ('Principal', $g, 3, $seccion->seccion);
					$libro_ods->addStringCell ('Principal', $g, 4, $seccion->materia_departamento);
					$libro_ods->addStringCell ('Principal', $g, 5, $departamento->descripcion);
					$libro_ods->addStringCell ('Principal', $g, 6, $seccion->maestro_apellido);
					$libro_ods->addStringCell ('Principal', $g, 7, $seccion->maestro_nombre);
					$libro_ods->addStringCell ('Principal', $g, 8, $seccion->maestro);
					$libro_ods->addStringCell ('Principal', $g, 9, $hora->inicio);
					$libro_ods->addStringCell ('Principal', $g, 10, $hora->fin);
					$libro_ods->addStringCell ('Principal', $g, 11, $hora->salon_edificio);
					$libro_ods->addStringCell ('Principal', $g, 12, $hora->salon_aula);
					
					$h = 13;
					foreach ($dias as $dia) {
						$libro_ods->addStringCell ('Principal', $g, $h, $hora->$dia);
						$h++;
					}
					$g++;
				}
			}
		}
		
		$libro_ods->construir_paquete ();
		
		return new Gatuf_HTTP_Response_File ($libro_ods->nombre, 'Oferta-'.$carrera->clave.'.ods', 'application/vnd.oasis.opendocument.spreadsheet', true);
	}
}
