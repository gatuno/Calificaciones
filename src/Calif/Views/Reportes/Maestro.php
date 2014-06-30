<?php
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
class Calif_Views_Reportes_Maestro {
	public function cargaHoraria ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Calif_Form_Reportes_Oferta_SeleccionarDepartamento ($request->POST);
			
			if ($form->isValid ()) {
				$departamento = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Reportes_Maestro::cargaHorariaPorDepartamento', array ($departamento->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Reportes_Oferta_SeleccionarDepartamento (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/reportes/oferta/seleccionar-departamento.html',
		                                          array ('form' => $form,
		                                                 'page_title' => 'Reporte de cargas horarias por departamento'),
		                                          $request);
	}
	
	public function cargaHorariaPorDepartamento ($request, $match) {
		$departamento = new Calif_Departamento ();
		
		if (false === ($departamento->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$sql = new Gatuf_SQL ('departamento=%s', $departamento->clave);
		
		$maestros = Gatuf::factory ('Calif_Maestro')->getList (array ('filter' => $sql->gen (), 'view' => 'maestros_departamentos'));
		
		$nombramientos = array ();
		foreach (Gatuf::factory ('Calif_Nombramiento')->getList () as $nomb) {
			$nombramientos[$nomb->clave] = $nomb;
		}
		
		$cargas = array ();
		foreach ($maestros as $maestro) {
			$cargas[$maestro->codigo] = array ();
			
			$cargas[$maestro->codigo]['maxT'] = $maestro->maxTiempoCompleto ();
			$cargas[$maestro->codigo]['maxA'] = $maestro->maxAsignatura ();
			
			$cargas[$maestro->codigo]['T'] = $maestro->getCarga ('t');
			$cargas[$maestro->codigo]['A'] = $maestro->getCarga ('a');
			$cargas[$maestro->codigo]['H'] = $maestro->getCarga ('h');
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/reportes/maestro/carga-horaria.html',
		                                          array ('departamento' => $departamento,
		                                                 'cargas' => $cargas,
		                                                 'maestros' => $maestros,
		                                                 'nombramientos' => $nombramientos,
		                                                 'page_title' => 'Reporte de cargas horarias por departamento'),
		                                          $request);
	}
	
	public function suplencias ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Calif_Form_Reportes_Oferta_SeleccionarDepartamento ($request->POST);
			
			if ($form->isValid ()) {
				$departamento = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Reportes_Maestro::suplenciasPorDepartamento', array ($departamento->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Reportes_Oferta_SeleccionarDepartamento (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/reportes/oferta/seleccionar-departamento.html',
		                                          array ('form' => $form,
		                                                 'page_title' => 'Reporte de suplencias por departamento'),
		                                          $request);
	}
	
	public function suplenciasPorDepartamento ($request, $match) {
		$departamento = new Calif_Departamento ();
		
		if (false === ($departamento->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$sql = new Gatuf_SQL ('materia_departamento=%s AND suplente IS NOT NULL', $departamento->clave);
		
		$secciones = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen (), 'view' => 'paginador'));
		
		$libro_ods = new Gatuf_ODS ();
		
		$libro_ods->addNewSheet ('Principal');
		$libro_ods->addStringCell ('Principal', 1, 1, 'NRC');
		$libro_ods->addStringCell ('Principal', 1, 2, 'Clave de la materia');
		$libro_ods->addStringCell ('Principal', 1, 3, 'Descripcion');
		$libro_ods->addStringCell ('Principal', 1, 4, 'Seccion');
		$libro_ods->addStringCell ('Principal', 1, 5, 'Clave departamento');
		$libro_ods->addStringCell ('Principal', 1, 6, 'Departamento');
		$libro_ods->addStringCell ('Principal', 1, 7, 'Apellido del profesor Titular');
		$libro_ods->addStringCell ('Principal', 1, 8, 'Nombre del profesor Titular');
		$libro_ods->addStringCell ('Principal', 1, 9, 'Codigo del profesor Titular');
		$libro_ods->addStringCell ('Principal', 1, 10, 'Apellido del profesor Suplente');
		$libro_ods->addStringCell ('Principal', 1, 11, 'Nombre del profesor Suplente');
		$libro_ods->addStringCell ('Principal', 1, 12, 'Codigo del profesor Suplente');
		$libro_ods->addStringCell ('Principal', 1, 13, 'Horas Teoria');
		$libro_ods->addStringCell ('Principal', 1, 14, 'Carga suplente de horas teoria');
		$libro_ods->addStringCell ('Principal', 1, 15, 'Horas Practica');
		$libro_ods->addStringCell ('Principal', 1, 16, 'Carga suplente de horas practica');
		
		$g = 2;
		
		foreach ($secciones as $seccion) {
			$suplente = $seccion->get_suplente ();
			$materia = $seccion->get_materia ();
			
			$libro_ods->addStringCell ('Principal', $g, 1, $seccion->nrc);
			$libro_ods->addStringCell ('Principal', $g, 2, $seccion->materia);
			$libro_ods->addStringCell ('Principal', $g, 3, $seccion->materia_desc);
			$libro_ods->addStringCell ('Principal', $g, 4, $seccion->seccion);
			$libro_ods->addStringCell ('Principal', $g, 5, $seccion->materia_departamento);
			$libro_ods->addStringCell ('Principal', $g, 6, $departamento->descripcion);
			$libro_ods->addStringCell ('Principal', $g, 7, $seccion->maestro_apellido);
			$libro_ods->addStringCell ('Principal', $g, 8, $seccion->maestro_nombre);
			$libro_ods->addStringCell ('Principal', $g, 9, $seccion->maestro);
			$libro_ods->addStringCell ('Principal', $g, 10, $suplente->apellido);
			$libro_ods->addStringCell ('Principal', $g, 11, $suplente->nombre);
			$libro_ods->addStringCell ('Principal', $g, 12, $suplente->codigo);
			$libro_ods->addStringCell ('Principal', $g, 13, $materia->teoria);
			$libro_ods->addStringCell ('Principal', $g, 15, $materia->practica);
			
			if ($materia->teoria == 0) {
				$libro_ods->addStringCell ('Principal', $g, 14, 'No aplica');
			} else {
				$puesto = $seccion->get_calif_numeropuesto_list (array ('filter' => 'tipo="u"'));
				if ($puesto[0]->carga == 'a') {
					$cargado = 'Asignatura';
				} else if ($puesto[0]->carga == 't') {
					$cargado = 'Sobre su tiempo completo/medio tiempo';
				} else if ($puesto[0]->carga == 'h') {
					$cargado = 'Honorífico';
				}
				$libro_ods->addStringCell ('Principal', $g, 14, $cargado);
			}
			
			if ($materia->practica == 0) {
				$libro_ods->addStringCell ('Principal', $g, 16, 'No aplica');
			} else {
				$puesto = $seccion->get_calif_numeropuesto_list (array ('filter' => 'tipo="q"'));
				if ($puesto[0]->carga == 'a') {
					$cargado = 'Asignatura';
				} else if ($puesto[0]->carga == 't') {
					$cargado = 'Sobre su tiempo completo/medio tiempo';
				} else if ($puesto[0]->carga == 'h') {
					$cargado = 'Honorífico';
				}
				$libro_ods->addStringCell ('Principal', $g, 16, $cargado);
			}
			
			$g++;
		}
		
		$libro_ods->construir_paquete ();
		
		return new Gatuf_HTTP_Response_File ($libro_ods->nombre, 'Suplencias-'.$departamento->clave.'.ods', 'application/vnd.oasis.opendocument.spreadsheet', true);
	}
}
