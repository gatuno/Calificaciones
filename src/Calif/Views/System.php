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
			
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_System::importarNrcInventado');
			if ($form->isValid ()) {
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
}
