<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Horario {
	public $agregarHora_precond = array ('Calif_Precondition::coordinadorRequired');
	public function agregarHora ($request, $match) {
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if (!$request->user->administrator) { // || !el otro permiso
			if (is_null ($seccion->asignacion) || !$request->user->hasPerm ('SIIAU.coordinador.'.$seccion->asignacion)) {
				$request->user->setMessage (3, 'No puede modificar una sección que no ha reclamado');
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		}
		
		$title = 'Agregar un nuevo horario';
		
		$extra = array('seccion' => $seccion);
		if ($request->method == 'POST') {
			$form = new Calif_Form_Horario_Agregar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$horario = $form->save ();
				
				/* Antes de guardar, verificar si la hora recién agregada colisiona con 
				 * otro salon */
				$sql = new Gatuf_SQL ('salon=%s', $horario->salon);
				$ors = array ();
				foreach (array ('l', 'm', 'i', 'j', 'v', 's', 'd') as $dia) {
					if ($horario->$dia) {
						$ors[] = $dia.'=1';
					}
				}
				$sql_dias = new Gatuf_SQL (implode (' OR ', $ors));
				$sql->SAnd ($sql_dias);
				$horas_salon = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen (), 'view' => 'paginador'));
				
				/* Recorrer estas horas */
				foreach ($horas_salon as $hora_en_salon) {
					if ($hora_en_salon->id == $horario->id) continue;
					if (Calif_Horario::chocan ($horario, $hora_en_salon)) {
						$request->user->setMessage (2, sprintf ('La hora agregada al nrc %s colisiona en el salon <a href="%s">%s %s</a>', $seccion->nrc, Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::verSalon', $horario->salon), $hora_en_salon->salon_edificio, $hora_en_salon->salon_aula));
						break;
					}
				}
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($horario->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Horario_Agregar (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/horario/agregar-horario.html',
		                                         array ('page_title' => $title,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
		
	}
	
	public $eliminarHora_precond = array ('Calif_Precondition::coordinadorRequired');
	public function eliminarHora ($request, $match) {
		$title = 'Eliminar hora de una sección';
		
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if (!$request->user->administrator) { // || !el otro permiso
			if (is_null ($seccion->asignacion) || !$request->user->hasPerm ('SIIAU.coordinador.'.$seccion->asignacion)) {
				$request->user->setMessage (3, 'No puede modificar una sección que no ha reclamado');
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			} else if ($seccion->nrc < 80000) {
				$request->user->setMessage (3, 'No puede modificar una sección ya existente, solo las nuevas secciones');
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		}
		
		$hora = new Calif_Horario ();
		
		if (false === ($hora->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($hora->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$salon = new Calif_Salon ();
		$salon->get ($hora->salon);
		
		if ($request->method == 'POST') {
			/* Adelante, eliminar esta hora */
			$hora->delete ();
			
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($seccion->nrc));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/horario/eliminar-horario.html',
		                                         array ('page_title' => $title,
		                                                'seccion' => $seccion,
		                                                'salon' => $salon,
		                                                'horario' => $hora),
		                                         $request);
	}
	
	public $actualizarHora_precond = array ('Calif_Precondition::coordinadorRequired');
	public function actualizarHora ($request, $match) {
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if (!$request->user->administrator) { // || !el otro permiso
			if (is_null ($seccion->asignacion) || !$request->user->hasPerm ('SIIAU.coordinador.'.$seccion->asignacion)) {
				$request->user->setMessage (3, 'No puede modificar una sección que no ha reclamado');
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			} else if ($seccion->nrc < 80000) {
				$request->user->setMessage (3, 'No puede modificar una sección ya existente, solo las nuevas secciones');
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		}
		
		$hora = new Calif_Horario ();
		
		if (false === ($hora->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($hora->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$title = 'Actualizar horario';
		
		$extra = array('seccion' => $seccion, 'horario' => $hora);
		if ($request->method == 'POST') {
			$form = new Calif_Form_Horario_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$horario = $form->save ();
				
				$sql = new Gatuf_SQL ('salon=%s', $horario->salon);
				$ors = array ();
				foreach (array ('l', 'm', 'i', 'j', 'v', 's', 'd') as $dia) {
					if ($horario->$dia) {
						$ors[] = $dia.'=1';
					}
				}
				$sql_dias = new Gatuf_SQL (implode (' OR ', $ors));
				$sql->SAnd ($sql_dias);
				$horas_salon = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen (), 'view' => 'paginador'));
				
				/* Recorrer estas horas */
				foreach ($horas_salon as $hora_en_salon) {
					if ($hora_en_salon->id == $horario->id) continue;
					if (Calif_Horario::chocan ($horario, $hora_en_salon)) {
						/* Choque, este salon está ocupado en la hora recién agregada */
						$request->user->setMessage (2, sprintf ('La hora agregada al nrc %s colisiona en el salon <a href="%s">%s %s</a>', $seccion->nrc, Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::verSalon', $horario->salon), $hora_en_salon->salon_edificio, $hora_en_salon->salon_aula));
						break;
					}
				}
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($horario->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Horario_Actualizar (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/horario/edit-horario.html',
		                                         array ('page_title' => $title,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
}
