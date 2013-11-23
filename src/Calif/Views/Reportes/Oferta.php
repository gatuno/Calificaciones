<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Reportes_Oferta {
	public $porCarrera_precond = array ('Calif_Precondition::coordinadorRequired');
	public function porCarrera ($request, $match) {
		$carrera = new Calif_Carrera ();
		
		if (false === ($carrera->get ($match[1]))) {
			if ($match[1] != 'DIVEC') {
				throw new Gatuf_HTTP_Error404 ();
			}
			
			$carrera->clave = 'DIVEC';
			$carrera->descripcion = 'División de electrónica y computación';
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
		if ($carrera->clave == 'DIVEC') {
			$ors = array ();
			foreach (array ('COM', 'INCO', 'INF', 'INNI', 'CEL', 'INCE', 'BIM', 'INBI') as $c) {
				$ors[] = sprintf ("asignacion='%s'", $c);
			}
			
			$sql = new Gatuf_SQL (implode (' OR ', $ors));
		} else {
			$sql = new Gatuf_SQL ('asignacion=%s', $carrera->clave);
		}
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
			if ($match[1] != 'DIVEC') {
				throw new Gatuf_HTTP_Error404 ();
			}
			
			$carrera->clave = 'DIVEC';
			$carrera->descripcion = 'División de electrónica y computación';
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
		
		/* Generar el reporte */
		if ($carrera->clave == 'DIVEC') {
			$ors = array ();
			foreach (array ('COM', 'INCO', 'INF', 'INNI', 'CEL', 'INCE', 'BIM', 'INBI') as $c) {
				$ors[] = sprintf ("asignacion='%s'", $c);
			}
			
			$sql = new Gatuf_SQL (implode (' OR ', $ors));
		} else {
			$sql = new Gatuf_SQL ('asignacion=%s', $carrera->clave);
		}
		$secciones = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen (), 'view' => 'paginador'));
		
		$dias = array ('l', 'm', 'i', 'j', 'v', 's');
		$g = 2;
		foreach ($secciones as $seccion) {
			$horas = $seccion->get_calif_horario_list (array ('view' => 'paginador'));
			$cant_horas = count ($horas);
			$departamento = new Calif_Departamento ($seccion->materia_departamento);
			
			if ($cant_horas == 0) {
				/* Imprimir, pero sin horas */
				$libro_ods->addStringCell ('Principal', $g, 1, $seccion->nrc);
				$libro_ods->addStringCell ('Principal', $g, 2, $seccion->materia);
				$libro_ods->addStringCell ('Principal', $g, 3, $seccion->materia_desc);
				$libro_ods->addStringCell ('Principal', $g, 4, $seccion->seccion);
				$libro_ods->addStringCell ('Principal', $g, 5, $seccion->materia_departamento);
				$libro_ods->addStringCell ('Principal', $g, 6, $departamento->descripcion);
				$libro_ods->addStringCell ('Principal', $g, 7, $seccion->maestro_apellido);
				$libro_ods->addStringCell ('Principal', $g, 8, $seccion->maestro_nombre);
				$libro_ods->addStringCell ('Principal', $g, 9, $seccion->maestro);
				
				for ($h = 10; $h < 20; $h++) {
					$libro_ods->addStringCell ('Principal', $g, $h, 'N/A');
				}
				
				$libro_ods->addStringCell ('Principal', $g, 20, $seccion->asignacion);
				$g++;
			} else {
				foreach ($horas as $hora) {
					/* Recuperar sus horas, e imprimirlas */
					$libro_ods->addStringCell ('Principal', $g, 1, $seccion->nrc);
					$libro_ods->addStringCell ('Principal', $g, 2, $seccion->materia);
					$libro_ods->addStringCell ('Principal', $g, 3, $seccion->materia_desc);
					$libro_ods->addStringCell ('Principal', $g, 4, $seccion->seccion);
					$libro_ods->addStringCell ('Principal', $g, 5, $seccion->materia_departamento);
					$libro_ods->addStringCell ('Principal', $g, 6, $departamento->descripcion);
					$libro_ods->addStringCell ('Principal', $g, 7, $seccion->maestro_apellido);
					$libro_ods->addStringCell ('Principal', $g, 8, $seccion->maestro_nombre);
					$libro_ods->addStringCell ('Principal', $g, 9, $seccion->maestro);
					$libro_ods->addStringCell ('Principal', $g, 10, $hora->inicio);
					$libro_ods->addStringCell ('Principal', $g, 11, $hora->fin);
					$libro_ods->addStringCell ('Principal', $g, 12, $hora->salon_edificio);
					$libro_ods->addStringCell ('Principal', $g, 13, $hora->salon_aula);
					
					$h = 14;
					foreach ($dias as $dia) {
						$libro_ods->addStringCell ('Principal', $g, $h, $hora->$dia);
						$h++;
					}
					$libro_ods->addStringCell ('Principal', $g, 20, $seccion->asignacion);
					$g++;
				}
			}
		}
		
		$libro_ods->construir_paquete ();
		
		return new Gatuf_HTTP_Response_File ($libro_ods->nombre, 'Oferta-'.$carrera->clave.'.ods', 'application/vnd.oasis.opendocument.spreadsheet', true);
	}
}
