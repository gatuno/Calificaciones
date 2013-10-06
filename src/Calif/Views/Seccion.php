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
		$pag->model_view = 'paginador';
		
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
		
		$alumnos = $seccion->getAlumnosList ();
		
		// Llenar arreglo $evaluacion[Grupos_Evaluaciones->id][Porcentajes->evaluacion]=Evaluaciones->descripcion
		$todas_evaluaciones = array ();
		
		foreach (Gatuf::factory('Calif_Evaluacion')->getList() as $e){
			$todas_evaluaciones[$e->id] = $e;
		}
		
		$evaluacion = array ();
		$grupo_evals = array ();
		$promedios = array ();
		$promedios_eval = array();
		foreach (Gatuf::factory('Calif_GrupoEvaluacion')->getList() as $geval) {
			$sql = new Gatuf_SQL ('materia=%s AND grupo=%s', array($materia->clave, $geval->id));
			$temp_eval = Gatuf::factory('Calif_Porcentaje')->getList(array ('filter' => $sql->gen()));
		
			if (count ($temp_eval) != 0) {
				$grupo_evals[$geval->id] = $geval;
				foreach($temp_eval as $t_eval) {
					$evaluacion[$geval->id][$t_eval->evaluacion] = $todas_evaluaciones[$t_eval->evaluacion]->descripcion;
				}
			}
		}
		
		// Llenar Arreglo $calificacion[calificaciones->alumno][calificaciones->evaluacion]=calificaciones->valor; 
		$array_eval = array(-1 => 'NP', -2 => 'SD');
		$calificacion = array();
		$sql = new Gatuf_SQL ('nrc=%s', $seccion->nrc);
		$temp_calif = Gatuf::factory('Calif_Calificacion')->getList(array('filter'=> $sql->gen()));

		foreach ($temp_calif as $t_calif) {
			if (array_key_exists($t_calif->valor , $array_eval)) {
				$t_calif->valor = $array_eval[$t_calif->valor];
			} else if($t_calif->valor) {
				$t_calif->valor .='%';
			}
			$calificacion[$t_calif->alumno][$t_calif->evaluacion] = $t_calif->valor;
			
			foreach ($grupo_evals as $id => $gp) {
				$promedios[$t_calif->alumno][$id] = $t_calif->getPromedio ($id);
			}
		}
		
		// LLenar arreglo con los promedios de la
		foreach($evaluacion as $key => $eval) {
				foreach($eval as $ev => $e) {
					if($promedios_eval[$key][$ev] = $temp_calif[0]->getPromedioEval ($ev)){
						$promedios_eval[$key][$ev] .= '%'; 
					}
				}
			}
		
		//Form Choise Evaluaciones
		$extra = array ("eval" =>$evaluacion);
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Evaluacion_Evaluar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$eval_form = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::evaluar', array ('nrc' => $seccion->nrc, 'evaluacion' => $eval_form ));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Evaluacion_Evaluar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/ver-seccion.html',
		                                          array ('seccion' => $seccion,
		                                                 'evaluacion' => $evaluacion,
		                                                 'grupo_evals' => $grupo_evals,
		                                                 'calificacion' => $calificacion,
		                                                 'page_title' => $title,
		                                                 'materia' => $materia,
		                                                 'maestro' => $maestro,
		                                                 'horarios' => $horarios,
		                                                 'promedios' => $promedios,
		                                                 'promedios_eval' => $promedios_eval,
		                                                 'form' => $form,
		                                                 'alumnos' => $alumnos),
		                                          $request);
	}
	
	public $agregarNrc_precond = array ('Calif_Precondition::coordinadorRequired');
	public function agregarNrc ($request, $match) {
		$title = 'Crear NRC';
		
		$extra = array ();
		
		if (isset ($match[1])) {
			$materia = new Calif_Materia ();
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
		
		if ($request->user->administrator) {
			/* Formulario completo para los administradores, o la otra condición */
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
		} else {
			/* El caso de los coordinadores */
			$extra['user'] = $request->user;
			if ($request->method == 'POST') {
				$form = new Calif_Form_Seccion_AgregarMini ($request->POST, $extra);
				
				if ($form->isValid ()) {
					$seccion = $form->save ();
					
					$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($seccion->nrc));
					return new Gatuf_HTTP_Response_Redirect ($url);
				}
			} else {
				$form = new Calif_Form_Seccion_AgregarMini (null, $extra);
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/agregar-seccion.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $agregarNrcConMateria_precond = array ('Calif_Precondition::coordinadorRequired');
	public function agregarNrcConMateria ($request, $match) {
		return $this->agregarNrc ($request, $match);
	}
	
	public $actualizarNrc_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarNrc ($request, $match) {
		$title = 'Actualizar NRC';
		
		$seccion = new Calif_Seccion ();
		
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
		
		$seccion = new Calif_Seccion ();
		
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
	
	public function evaluar ($request, $match){
		/* Se recibe:
		 * nrc en match[1]
		 * Evaluacion en match[2]
		 */
		$modo_eval = $match[2];
		$seccion =  new Calif_Seccion ();
		
		if (false === ($seccion->getNrc($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$sql = new Gatuf_SQL ('materia=%s AND evaluacion=%s', array ($seccion->materia, $modo_eval));
		$porcentaje = Gatuf::factory ('Calif_Porcentaje')->getList (array ('filter' => $sql->gen ()));
		$porcentaje= $porcentaje[0]->porcentaje;
		
		$eval_model = new Calif_Evaluacion ();
		$eval_model->getEval ($modo_eval);
		
		$title = 'Evaluacion de '.$eval_model->descripcion.' Para la seccion '.$seccion->nrc.' Evaluando sobre '.$porcentaje.' puntos';
		
		$extra = array ('nrc' => $seccion, 'modo_eval' => $modo_eval, 'porcentaje' => $porcentaje);
		if ($request->method == 'POST') {
			$form = new Calif_Form_Seccion_Evaluar ($request->POST, $extra);
			if ($form->isValid ()) {
				$form->save ();
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Seccion_Evaluar (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/evaluacion/seccion-eval.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
}
