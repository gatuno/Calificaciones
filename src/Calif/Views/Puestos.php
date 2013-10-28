<?php
Gatuf::loadFunction ('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction ('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Puestos {
	public function actualizarPuesto ($request, $match) {
		$puesto = new Calif_NumeroPuesto ();
		
		if (false === ($puesto->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$nrc = new Calif_Seccion ($puesto->nrc);
		$maestro = new Calif_Maestro ($nrc->maestro);
		$materia = new Calif_Materia ($nrc->materia);
		
		$extra = array ('puesto' => $puesto);
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Puesto_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$puesto = $form->save ();
				
				/* TODO: Recalcular el total de horas, y avisar cuando se pase */
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($puesto->nrc));
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Puesto_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/puesto/edit-puesto.html',
		                                         array ('page_title' => 'Actualizar número de puesto',
		                                                'puesto' => $puesto,
		                                                'materia' => $materia,
		                                                'maestro' => $maestro,
		                                                'seccion' => $nrc,
		                                                'form' => $form),
		                                         $request);
	}
}
