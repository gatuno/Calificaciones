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

	public function evaluarAlumno($request, $match){
			$nrc = $match[1];
			$clave = $match[2];
			$codigo = $match[3];
			$sql = new Gatuf_SQL ('codigo=%s', array( $codigo));
			$alumno = Gatuf::factory('Calif_Alumno')->getList(array ('filter' => $sql->gen()));
			$title = 'Evaluando a '.$alumno[0]->nombre.' '.$alumno[0]->apellido;
			$sql = new Gatuf_SQL ('materia=%s', array( $clave));
			$evaluaciones = $this->getEvalMateria($sql);
			$sql = new Gatuf_SQL ('alumno=%s', array( $codigo));
			$calificaciones = Gatuf::factory('Calif_Calificacion')->getList(array ('filter' => $sql->gen()));
			$calificacion =  array();
			foreach ($calificaciones as $key => $cali){
				$calificacion[$cali->evaluacion]=$cali->valor;
			}
			$extra = array('evaluacion'=>$evaluaciones, 'calificacion'=>$calificacion, 'nrc'=>$nrc, 'codigo'=>$codigo);
			if ($request->method == 'POST') {
			$form = new Calif_Form_Evaluacion_EvaluarAlumno ($request->POST, $extra);
			if ($form->isValid()) {
				$form->save ();
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Evaluacion_EvaluarAlumno (null, $extra);
			}	
		return Gatuf_Shortcuts_RenderToResponse ('calif/evaluacion/alumno-eval.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
		}

	private function getEvalMateria($sql, $opc=0){
		$tabla_evaluaciones = Gatuf::factory('Calif_Evaluacion')->getList();
		$todas_evaluaciones = array();
		foreach($tabla_evaluaciones as $e){
			$todas_evaluaciones[$e->id] = $e;
		}
		$evaluacion = array();
		$temp_eval =Gatuf::factory('Calif_Porcentaje')->getList(array ('filter' => $sql->gen()));
		foreach($temp_eval as $t_eval){
				$evaluacion[$t_eval->evaluacion] = $todas_evaluaciones[$t_eval->evaluacion]->descripcion;
				$porcentajes[$t_eval->evaluacion]=$t_eval->porcentaje;
			}
			if($opc > 0)
				$evaluacion = array_flip($evaluacion);
			$arreglo = array('eval'=>$evaluacion, 'porcentajes'=>$porcentajes);
			return $arreglo;
		}
	
	public function verEval ($request, $match) {
		return new Gatuf_HTTP_Response ('Evaluacion');
	}
}
