<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Edificio {
	
	public function index($request, $match) {
		$edificio = new Calif_Edificio ();
		
		$pag = new Gatuf_Paginator ($edificio);
		$pag->action = array ('Calif_Views_Edificio::index');
		$pag->summary = 'Lista de los edificios';
		
		$list_display = array (
			array ('clave', 'Gatuf_Paginator_FKLink', 'Clave Siiau'),
			array ('descripcion', 'Gatuf_Paginator_DisplayVal', 'Descripción')
		);
		
		$pag->items_per_page = 40;
		$pag->no_results_text = 'No hay edificios';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('clave', 'descripcion'),
			array ('clave', 'descripcion')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/edificio/index.html',
		                                         array('page_title' => 'Edificios',
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public function verEdificio ($request, $match) {
		Gatuf::loadFunction ('Calif_Utils_displayHoraSiiau');
		$edificio = new Calif_Edificio ();
		
		if (false === $edificio->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		/* Verificar que el edificio esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Edificio::verEdificio', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$salones = $edificio->get_calif_salon_list ();
		
		$super_calendarios = array ();
		foreach ($salones as $salon) {
			$sql = new Gatuf_SQL ('salon=%s', $salon->id);
			$horas_salon = $salon->get_calif_horario_list ();
			
			if (count ($horas_salon) == 0) {
				$super_calendarios[$salon->id] = null;
				continue;
			}
			$calendar = new Gatuf_Calendar ();
			$calendar->events = array ();
			$calendar->opts['conflicts'] = true;
			$calendar->opts['conflict-color'] = '#FF2828';
			
			$nrc = new Calif_Seccion ();
			foreach ($horas_salon as $horario) {
				$nrc->get ($horario->nrc);
				$cadena_desc = $nrc->materia . ' ' . $nrc->seccion.'<br />';
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $nrc->nrc);
				$dia_semana = strtotime ('next Monday');
				$calendar->opts['start-day'] = date('Y-m-d', $dia_semana);
				foreach (array ('l', 'm', 'i', 'v', 's') as $dia) {
					if ($horario->$dia) {
						$calendar->events[] = array ('start' => date('Y-m-d ', $dia_semana).$horario->inicio,
								                     'end' => date('Y-m-d ', $dia_semana).$horario->fin,
								                     'title' => $horario->nrc,
								                     'content' => $cadena_desc,
								                     'url' => $url, 'color' => '');
					}
					$dia_semana = $dia_semana + 86400;
				}
				$calendar->opts['end-day'] = date('Y-m-d', $dia_semana);
			}
			
			$super_calendarios[$salon->id] = $calendar;
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/edificio/ver-edificio.html',
		                                         array('page_title' => 'Ver edificio',
		                                               'edificio' => $edificio,
		                                               'salones' => $salones,
                                                       'calendarios' => $super_calendarios),
                                                 $request);
	}
}
