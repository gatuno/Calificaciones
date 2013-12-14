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
}
