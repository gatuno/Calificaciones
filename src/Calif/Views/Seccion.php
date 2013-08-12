<?php

/*Pluf::loadFunction('Pluf_HTTP_URL_urlForView');
Pluf::loadFunction('Pluf_Shortcuts_GetObjectOr404');
Pluf::loadFunction('Pluf_Shortcuts_GetFormForModel');*/
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Seccion {
	
	public function index ($request, $match) {
		/* Utilizar un paginador aquí, por favor */
		$seccion = new Calif_Seccion ();
		
		/* Enlaces extras */
		$pag = new Gatuf_Paginator ($seccion);
		
		$pag->action = array ('Calif_Views_Seccion::index');
		$pag->summary = 'Lista de secciones';
		$list_display = array (
			array ('nrc', 'Gatuf_Paginator_FKLink', 'NRC'),
			array ('materia', 'Gatuf_Paginator_FKLink', 'Materia'),
			array ('seccion', 'Gatuf_Paginator_FKLink', 'Sección'),
			array ('maestro_apellido', 'Gatuf_Paginator_FKLink', 'Maestro'),
		);
		
		$pag->items_per_page = 30;
		$pag->no_results_text = 'No se encontraron secciones';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('nrc', 'materia', 'seccion', 'materia_desc', 'maestro_nombre', 'maestro_apellido'),
			array ('nrc', 'materia', 'seccion', 'maestro_apellido')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/index.html',
		                                          array ('paginador' => $pag,
		                                                 'page_title' => 'Secciones'),
		                                          $request);
	}
	
	public function verNrc ($request, $match) {
		$title = 'Ver NRC';
		
		$seccion =  new Calif_Seccion ();
		
		if (false === ($seccion->getNrc($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$materia = new Calif_Materia ();
		$materia->getMateria ($seccion->materia);
		
		$maestro = new Calif_Maestro ();
		$maestro->getMaestro ($seccion->maestro);
		
		$sql = new Gatuf_SQL ('nrc=%s', $seccion->nrc);
		
		$horarios = Gatuf::factory('Calif_Horario')->getList (array ('filter' => $sql->gen ()));
		
		/* FIXME: Resolver con relación N-M de vista */
		$lista = $seccion->getAlumnos ();
		
		$alumnos = array ();
		$alumno = new Calif_Alumno ();
		foreach ($lista as $al) {
			$alumno->getAlumno ($al['alumno']);
			$alumnos[] = clone ($alumno);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/ver-seccion.html',
		                                          array ('seccion' => $seccion,
		                                                 'page_title' => $title,
		                                                 'materia' => $materia,
		                                                 'maestro' => $maestro,
		                                                 'horarios' => $horarios,
		                                                 'alumnos' => $alumnos),
		                                          $request);
	}
	
	public $agregarNrc_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarNrc ($request, $match) {
		$title = 'Crear NRC';
		
		$extra = array ();
		
		if (isset ($match[1])) {
			$materia =  new Calif_Materia ();
			if (false === ($materia->getMateria($match[1]))) {
				throw new Gatuf_HTTP_Error404();
			}
		
			$nueva_clave = mb_strtoupper ($match[1]);
			if ($match[1] != $nueva_clave) {
				$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Seccion::agregarNrcConMateria', array ($nueva_clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
			$extra['materia'] = $materia->clave;
		}
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Seccion_Agregar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$seccion = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Seccion_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/edit-seccion.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $agregarNrcConMateria_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarNrcConMateria ($request, $match) {
		return $this->agregarNrc ($request, $match);
	}
	
	public $actualizarNrc_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarNrc ($request, $match) {
		$title = 'Actualizar NRC';
		
		$seccion =  new Calif_Seccion ();
		
		if (false === ($seccion->getNrc($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('seccion' => $seccion);
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Seccion_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$seccion = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Seccion_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/edit-seccion.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $eliminarNrc_precond = array ('Gatuf_Precondition::adminRequired');
	public function eliminarNrc ($request, $match) {
		$title = 'Eliminar NRC';
		
		$seccion =  new Calif_Seccion ();
		
		if (false === ($seccion->getNrc($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$materia = new Calif_Materia ();
		$materia->getMateria ($seccion->materia);
		
		if ($request->method == 'POST') {
			
			/* Eliminar todos los alumnos de este grupo */
			$seccion->clearAlumnos ();
			
			$sql = new Gatuf_SQL ('nrc=%s', $seccion->nrc);
			
			/* Eliminar todas los horarios dde esta sección */
			$horas = Gatuf::factory ('Calif_Horario')->getList (array ('filter' => $sql->gen ()));
			
			foreach ($horas as $hora) {
				$hora->delete ();
			}
			
			$seccion->delete ();
			$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::index');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/eliminar-seccion.html',
		                                         array ('page_title' => $title,
		                                                'materia' => $materia,
		                                                'seccion' => $seccion),
		                                         $request);
	}
	
	public $reclamarNrc_precond = array ('Calif_Precondition::coordinadorRequired');
	public function reclamarNrc ($request, $match) {
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->getNrc ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
		
		$carrera_a_reclamar = new Calif_Carrera ();
		
		if (false === ($carrera_a_reclamar->getCarrera ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Verificar que la materia pertenezca a la carrera */
		$materia = new Calif_Materia ();
		$materia->getMateria ($seccion->materia);
		
		$carreras = $materia->getCarrerasList ();
		
		$permiso = false;
		
		foreach ($carreras as $c) {
			if ($carrera_a_reclamar->clave == $c->clave) $permiso = true;
		}
		
		if ($permiso == false) {
			$request->user->setMessage (2, sprintf ('No puede reclamar esta sección. La materia "%s" no pertenece a la carrera %s', $materia->descripcion, $carrera_a_reclamar->descripcion));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		 /* Verificar que el maestro sea coordinador de la carrera que quiere reclamar */
		if (!$request->user->hasPerm ('SIIAU.coordinador.'.$carrera_a_reclamar->clave)) {
			$request->user->setMessage (2, 'No puede reclamar secciones para '.$carrera_a_reclamar->clave.'. Usted no es coordinador de esta carrera');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Si ya está asignado, marcar error */
		if (!is_null ($seccion->asignacion)) {
			$request->user->setMessage (2, 'La sección ya ha sido reclamada por '.$seccion->asignacion);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Ahora, intentar asignar el nrc */
		$seccion->asignacion = $carrera_a_reclamar->clave;
		
		if ($seccion->updateAsignacion () === true) {
			$request->user->setMessage (1, 'La sección '.$seccion->nrc.' ha sido marcada para la carrera '.$carrera_a_reclamar->clave);
		} else {
			$request->user->setMessage (2, 'La sección '.$seccion->nrc.' no pudo ser reclamada. Por favor intentelo otra vez');
		}
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $liberarNrc_precond = array ('Calif_Precondition::coordinadorRequired');
	public function liberarNrc ($request, $match) {
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->getNrc ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
		
		if (is_null ($seccion->asignacion)) {
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if (!$request->user->hasPerm ('SIIAU.coordinador.'.$seccion->asignacion)) {
			$request->user->setMessage (2, 'No puede liberar la sección '.$seccion->nrc.', usted no es el coordinador que la solicitó.');
		} else {
			$seccion->liberarAsignacion ();
			$request->user->setMessage (1, 'La sección '.$seccion->nrc.' ha sido liberada.');
		}
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
}
