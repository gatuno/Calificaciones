<?php

class Calif_Views_Evaluacion {
	public function index ($request, $match) {
		$eval = new Calif_Evaluacion ();
		$evals = $eval->getList();
		$grupos_evals = $eval->getGruposEvals ();
		
		$context = new Gatuf_Template_Context(array('page_title' => 'Evaluaciones',
                                                   'evals' => $evals,
                                                   'grupos_evals' => $grupos_evals)
                                            );
		$tmpl = new Gatuf_Template('calif/evaluacion/index.html');
		return new Gatuf_HTTP_Response($tmpl->render($context));
	}
	
	public function agregarEval ($request, $match) {
		$title = 'Nueva evaluacion';
		
		$extra = array ();
		
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
		
		$tmpl = new Gatuf_Template ('calif/evaluacion/edit-eval.html');
		$context = new Gatuf_Template_Context (array ('page_title' => $title, 'form' => $form));
		return new Gatuf_HTTP_Response ($tmpl->render ($context));
	}
	
	public function verEval ($request, $match) {
		return new Gatuf_HTTP_Response ('Evaluacion');
	}
}
