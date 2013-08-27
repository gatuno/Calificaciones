<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Salon {
	
	public function index ($request, $match) {
		/* Listar todos los salones aquí */
		
		$salon = new Calif_Salon ();
		
		$pag = new Gatuf_Paginator ($salon);
		$pag->action = array ('Calif_Views_Salon::index');
		$pag->summary = 'Lista de los salones';
		
		$list_display = array (
			array ('edificio', 'Gatuf_Paginator_FKLink', 'Edificio'),
			array ('aula', 'Gatuf_Paginator_FKLink', 'Aula'),
			array ('cupo', 'Gatuf_Paginator_DisplayVal', 'Cupo')
		);
		
		$pag->items_per_page = 40;
		$pag->no_results_text = 'No hay salones';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('edificio', 'aula'),
			array ('edificio', 'aula')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/salon/index.html',
		                                         array('page_title' => 'Salones',
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public function verSalon ($request, $match) {
		Gatuf::loadFunction ('Calif_Utils_displayHoraSiiau');
		$salon = new Calif_Salon ();
		
		if (false === ($salon->getSalonById ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$sql = new Gatuf_SQL ('salon=%s', $salon->id);
		$horas_salon = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen()));
		
		$calendar = new Gatuf_Calendar ();
		$calendar->events = array ();
		$calendar->opts['conflicts'] = true;
		$calendar->opts['conflict-color'] = '#FF2828';
		
		$nrc = new Calif_Seccion ();
		foreach ($horas_salon as $horario) {
			$nrc->getNrc ($horario->nrc);
			$cadena_desc = $nrc->materia . ' ' . $nrc->seccion.'<br />';
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $nrc->nrc);
			$dia_semana = strtotime ('next Monday');
			foreach (array ('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') as $dia) {
				if ($horario->$dia) {
					$calendar->events[] = array ('start' => date('Y-m-d ', $dia_semana).Calif_Utils_displayHoraSiiau ($horario->hora_inicio),
							                     'end' => date('Y-m-d ', $dia_semana).Calif_Utils_displayHoraSiiau ($horario->hora_fin),
							                     'title' => $horario->nrc,
							                     'content' => $cadena_desc,
							                     'url' => $url, 'color' => '');
				}
				$dia_semana = $dia_semana + 86400;
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/salon/ver-salon.html',
		                                         array('page_title' => 'Ver salon',
		                                               'salon' => $salon,
                                                       'calendar' => $calendar),
                                                 $request);
	}

	public function agregarSalon ($request, $match) {
		$title = 'Nuevo salon';

		$extra = array ();

		if ($request->method == 'POST') {
			$form = new Calif_Form_Salon_Agregar ($request->POST, $extra);

			if ($form->isValid ()) {
				$salon = $form->save ();

				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::verSalon', $salon->id);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Salon_Agregar (null, $extra);
		}

		return Gatuf_Shortcuts_RenderToResponse ('calif/salon/edit-salon.html',
		                                         array('page_title' => $title,
		                                               'form' => $form,),
                                                 $request);
	}
	
	public function actualizarSalon ($request, $match) {
		$title = 'Actualizar un salon';
		
		$salon = new Calif_Salon ();
		
		if (false === ($salon->getSalonById ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ();
		$extra['salon'] = $salon;
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Salon_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$salon = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::verSalon', $salon->id);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Salon_Actualizar (null, $extra);
		}

		return Gatuf_Shortcuts_RenderToResponse ('calif/salon/edit-salon.html',
		                                         array('page_title' => $title,
		                                               'form' => $form,),
                                                 $request);
	}
	
	function buscarSalon ($request, $match) {
		$title = 'Buscar salon vacio';
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Salon_Buscarsalon ($request->POST, null);
			
			if ($form->isValid ()) {
				$libres = $form->save ();
				
				return Gatuf_Shortcuts_RenderToResponse ('calif/salon/reporte-vacios.html',
		                                         array ('page_title' => $title,
		                                         'bus_inicio' => $form->cleaned_data['horainicio'],
		                                         'bus_fin' => $form->cleaned_data['horafin'],
		                                         'semana' => implode (',', $form->semana),
		                                         'salones' => $libres),
		                                         $request);
			}
		} else {
			$form = new Calif_Form_Salon_Buscarsalon (null, null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/salon/buscar-salon.html',
		                                         array ('page_title' => $title,
		                                         'form' => $form),
		                                         $request);
	}
}
