<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Evaluacion {
	public function index ($request, $match) {
		$evals = array ();
		$grupos_evals = Gatuf::factory('Calif_GrupoEvaluacion')->getList ();
		
		foreach ($grupos_evals as $grupo) {
			$evals [$grupo->id] = $grupo->get_calif_evaluacion_list ();
			if ($evals [$grupo->id]->count () == 0) $evals [$grupo->id] = array ();
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/evaluacion/index.html',
		                                         array('page_title' => 'Evaluaciones',
                                                       'evals' => $evals,
                                                       'grupos_evals' => $grupos_evals),
                                                 $request);
	}
	
	public $agregarEval_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarEval ($request, $match) {
		$title = 'Nueva evaluación';
		
		$extra = array ();
		if (isset ($request->REQUEST['grupo']) && $request->REQUEST['grupo'] != '') {
			$extra['grupo'] = $request->REQUEST['grupo'];
		}
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Evaluacion_Agregar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$evaluacion = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Evaluacion::verEval', array ($evaluacion->id));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Evaluacion_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/evaluacion/agregar-eval.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $actualizarEval_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarEval ($request, $match) {
		$title = 'Actualizar evaluación';
		$evaluacion = new Calif_Evaluacion ();
		
		if (false === ($evaluacion->get ($match [1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$grupo = new Calif_GrupoEvaluacion ($evaluacion->grupo);
		
		$extra = array ('evaluacion' => $evaluacion);
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Evaluacion_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$evaluacion = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Evaluacion::verEval', array ($evaluacion->id));
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Evaluacion_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/evaluacion/edit-eval.html',
		                                         array ('page_title' => $title,
		                                                'grupo' => $grupo,
		                                                'evaluacion' => $evaluacion,
		                                                'form' => $form),
		                                         $request);
	}
	
	public function verEval ($request, $match) {
		return new Gatuf_HTTP_Response ('Evaluacion');
	}
}
