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
		$title = 'Buscar salon vacio';
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Salon_Buscarsalon ($request->POST, null);
			
			if ($form->isValid ()) {
				$libres = $form->save ();
				$semana = array ();
				$dias = array ('l' => 'lunes', 'm' => 'martes', 'i' => 'miércoles', 'j' => 'jueves', 'v' => 'viernes', 's' => 'sábado');
				foreach ($form->semana as $dia) {
					$semana[] = $dias[$dia];
				}
				return Gatuf_Shortcuts_RenderToResponse ('calif/salon/reporte-vacios.html',
		                                         array ('page_title' => $title,
		                                         'bus_inicio' => $form->cleaned_data['horainicio'],
		                                         'bus_fin' => $form->cleaned_data['horafin'],
		                                         'semana' => implode (',', $semana),
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
