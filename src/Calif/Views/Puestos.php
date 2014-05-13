<?php
Gatuf::loadFunction ('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction ('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Puestos {
	public $actualizarPuesto_precond = array ('Calif_Precondition::jefeRequired');
	public function actualizarPuesto ($request, $match) {
		$puesto = new Calif_NumeroPuesto ();
		
		if (false === ($puesto->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$nrc = $puesto->get_nrc ();
		$maestro = $nrc->get_maestro ();
		$materia = $nrc->get_materia ();
		$suplente = null;
		if ($nrc->suplente != null) $suplente = $nrc->get_suplente ();
		
		if (!$request->user->hasPerm ('SIIAU.jefe.'.$materia->departamento)) {
			$request->user->setMessage (3, 'No puede modificar este número de puesto. Usted no es el jefe de departamento de esta materia');
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($puesto->nrc));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
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
		                                                'suplente' => $suplente,
		                                                'seccion' => $nrc,
		                                                'form' => $form),
		                                         $request);
	}
}
