<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Evaluacion {
	public function index ($request, $match) {
		$evals = array ();
		$grupos_evals = Gatuf::factory('Calif_Evaluacion')->getGruposEvals ();
		
		foreach ($grupos_evals as $grupo => $desc) {
			$sql_filter = new Gatuf_SQL ('grupo=%s', $grupo);
			$evals [$grupo] = Gatuf::factory ('Calif_Evaluacion')->getList (array ('filter' => $sql_filter->gen()));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/evaluacion/index.html',
		                                         array('page_title' => 'Evaluaciones',
                                                       'evals' => $evals,
                                                       'grupos_evals' => $grupos_evals),
                                                 $request);
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
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/evaluacion/edit-eval.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
	
	public function verEval ($request, $match) {
		return new Gatuf_HTTP_Response ('Evaluacion');
	}

	public function evaluar ($request, $match){
		/* Se recibe:
		 * modo evaluacion en match[1]
		 * nrc en match[2]
		* alumnos en match[3]
		 */
		echo $match[1];
}
