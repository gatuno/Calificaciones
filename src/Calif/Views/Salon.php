<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Salon {
	public $agregarSalon_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarSalon ($request, $match) {
		$title = 'Nuevo salon';
		$extra = array ();
		$extra['edificio'] = $match[1];

		if ($request->method == 'POST') {
			$form = new Calif_Form_Salon_Agregar ($request->POST, $extra);

			if ($form->isValid ()) {
				$salon = $form->save ();

				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Edificio::verEdificio', $salon->edificio).'#salon_'.$salon->id;
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Salon_Agregar (null, $extra);
		}

		return Gatuf_Shortcuts_RenderToResponse ('calif/salon/agregar-salon.html',
		                                         array('page_title' => $title,
		                                               'form' => $form,),
                                                 $request);
	}
	
	public $actualizarSalon_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarSalon ($request, $match) {
		$title = 'Actualizar un salon';
		
		$salon = new Calif_Salon ();
		
		if (false === ($salon->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('salon' => $salon);
		
		$edificio = new Calif_Edificio ($salon->edificio);
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Salon_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$salon = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Edificio::verEdificio', $salon->edificio).'#salon_'.$salon->id;
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Salon_Actualizar (null, $extra);
		}

		return Gatuf_Shortcuts_RenderToResponse ('calif/salon/edit-salon.html',
		                                         array('page_title' => $title,
		                                               'edificio' => $edificio,
		                                               'salon' => $salon,
		                                               'form' => $form,),
                                                 $request);
	}
	
	function buscarSalon ($request, $match) {
		/* Tratar de "pre-seleccionar" los edificios */
		$extra = array ('edificios' => null);
		
		if (isset ($request->GET['edificio']) && $request->GET['edificio'] != '') {
			$edificio = new Calif_Edificio ();
			if (false !== $edificio->get ($request->GET['edificio'])) {
				$extra['edificios'] = array ($edificio->clave);
			}
		}
		
		$extra['edificios'] = $request->session->getData ('buscar-edificios', $extra['edificios']);
		
		if ($extra['edificios'] === null) {
			$extra['edificios'] = Gatuf::config ('buscar-edificios', array ());
		}
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Salon_BuscarSalon ($request->POST, $extra);
			
			if ($form->isValid ()) {
				/* Antes de redireccionar, guardar los edificios preseleccionados */
				$data = $form->save ();
				$request->session->setData ('buscar-edificios', $data['edificios']);
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::reporteBuscados', array (), $request->POST, false);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Salon_BuscarSalon (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/salon/buscar-salon.html',
		                                         array ('page_title' => 'Buscar salon vacio',
		                                         'form' => $form),
		                                         $request);
	}
	
	function reporteBuscados ($request, $match) {
		Gatuf::loadFunction ('Calif_Utils_buscarSalonVacio');
		
		$form = new Calif_Form_Salon_BuscarSalon ($request->GET, null);
		
		if (!$form->isValid ()) {
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::buscarSalon');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$data = $form->save ();
		$libres = Calif_Utils_buscarSalonVacio ($data['semana'], $data['hora_inicio'], $data['hora_fin'], $data['edificios']);
		
		$semana = array ();
		$dias = array ('l' => 'lunes', 'm' => 'martes', 'i' => 'miércoles', 'j' => 'jueves', 'v' => 'viernes', 's' => 'sábado');
		foreach ($form->semana as $dia) {
			$semana[] = $dias[$dia];
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/salon/reporte-vacios.html',
		                                         array ('page_title' => 'Salones encontrados',
		                                         'bus_inicio' => $data['hora_inicio'],
		                                         'bus_fin' => $data['hora_fin'],
		                                         'semana' => implode (',', $semana),
		                                         'salones' => $libres),
		                                         $request);
	}
}
