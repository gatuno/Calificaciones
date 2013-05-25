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
			array ('edificio', 'Gatuf_Paginator_DisplayVal', 'Edificio'),
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
			throw new Pluf_HTTP_Error404();
		}
		
		$sql = new Gatuf_SQL ('salon=%s', $salon->id);
		$horas_salon = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen()));
		
		$calendar = new Gatuf_Calendar ();
		$calendar->events = array ();
		
		$nrc = new Calif_Seccion ();
		foreach ($horas_salon as $horario) {
			$nrc->getNrc ($horario->nrc);
			$cadena_desc = $nrc->materia . ' ' . $nrc->seccion.'<br />';
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $nrc->nrc);
			if ($horario->lunes) {
				$calendar->events[] = array ('start' => '2013-05-27 '.Calif_Utils_displayHoraSiiau ($horario->hora_inicio),
				                             'end' => '2013-05-27 '.Calif_Utils_displayHoraSiiau ($horario->hora_fin),
				                             'title' => $horario->nrc,
				                             'content' => $cadena_desc,
				                             'url' => $url, 'color' => '');
			}
			
			/* FIXME: Hacer un for o algo para todos los dias */
			if ($horario->martes) {
				$calendar->events[] = array ('start' => '2013-05-28 '.Calif_Utils_displayHoraSiiau ($horario->hora_inicio),
				                             'end' => '2013-05-28 '.Calif_Utils_displayHoraSiiau ($horario->hora_fin),
				                             'title' => $horario->nrc,
				                             'content' => $cadena_desc,
				                             'url' => $url, 'color' => '');
			}
			if ($horario->miercoles) {
				$calendar->events[] = array ('start' => '2013-05-29 '.Calif_Utils_displayHoraSiiau ($horario->hora_inicio),
				                             'end' => '2013-05-29 '.Calif_Utils_displayHoraSiiau ($horario->hora_fin),
				                             'title' => $horario->nrc,
				                             'content' => $cadena_desc,
				                             'url' => $url, 'color' => '');
			}
			
			if ($horario->jueves) {
				$calendar->events[] = array ('start' => '2013-05-30 '.Calif_Utils_displayHoraSiiau ($horario->hora_inicio),
				                             'end' => '2013-05-30 '.Calif_Utils_displayHoraSiiau ($horario->hora_fin),
				                             'title' => $horario->nrc,
				                             'content' => $cadena_desc,
				                             'url' => $url, 'color' => '');
			}
			if ($horario->viernes) {
				$calendar->events[] = array ('start' => '2013-05-31 '.Calif_Utils_displayHoraSiiau ($horario->hora_inicio),
				                             'end' => '2013-05-31 '.Calif_Utils_displayHoraSiiau ($horario->hora_fin),
				                             'title' => $horario->nrc,
				                             'content' => $cadena_desc,
				                             'url' => $url, 'color' => '');
			}
			if ($horario->sabado) {
				$calendar->events[] = array ('start' => '2013-06-01 '.Calif_Utils_displayHoraSiiau ($horario->hora_inicio),
				                             'end' => '2013-06-01 '.Calif_Utils_displayHoraSiiau ($horario->hora_fin),
				                             'title' => $horario->nrc,
				                             'content' => $cadena_desc,
				                             'url' => $url, 'color' => '');
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/salon/ver-salon.html',
		                                         array('page_title' => 'Ver salon',
		                                               'salon' => $salon,
                                                       'calendar' => $calendar),
                                                 $request);
	}
}
