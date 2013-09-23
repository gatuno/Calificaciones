<?php

class Calif_Form_Alumno_Evaluar extends Gatuf_Form {
	public $nrc, $alumno;
	public $grupo_eval;
	public $porcentajes;
	public $calificaciones;
	
	public function initFields($extra=array()) {
		$this->nrc = $extra['nrc'];
		$this->alumno = $extra['alumno'];
		$this->grupo_eval = $extra['grupo_eval'];
		
		$sql = new Gatuf_SQL ('materia=%s AND grupo=%s', array ($this->nrc->materia, $this->grupo_eval->id));
		$lista_porcentajes = Gatuf::factory ('Calif_Porcentaje')->getList (array ('filter' => $sql->gen ()));
		
		$sql = new Gatuf_SQL ('alumno=%s AND nrc=%s', array ($this->alumno->codigo, $this->nrc->nrc));
		
		$cadena_sql_base = $sql->gen();
		$eval_model = new Calif_Evaluacion ();
		$calif_model = new Calif_Calificacion ();
		foreach ($lista_porcentajes as $p) {
			$eval_model->getEval ($p->evaluacion);
			
			$sql = $cadena_sql_base.sprintf (' AND evaluacion=%s', $p->evaluacion);
			$calif_model->getCalif ($sql);
			
			$this->fields['calif_'.$p->evaluacion] = new Gatuf_Form_Field_Varchar (
				array (
					'required' => false,
					'label' => $eval_model->descripcion,
					'initial' => is_null ($calif_model->valor) ? '' : $calif_model->valor.'%',
					'help_text' => 'Evaluando sobre '.$p->porcentaje.' puntos',
					'max_length' => 6,
					'widget_attrs' => array (
						'maxlength' => 6,
					),
			));
		}
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		throw new Exception ('Alto');
		foreach($this->gruposeval as $key => $eval){
			$valor = $this->cleaned_data[$key];
			if($this->cleaned_data[$key]){
				if(!$pos = strpos($valor, '%') ){
					$valor = ($valor*100)/$this->porcentajes[$key];
				}
				$calificacion = new Calif_Calificacion();
				$calificacion->nrc = $this->nrc;
				$calificacion->alumno = $this->codigo;
				$calificacion->evaluacion = $key;
				$calificacion->valor = $valor;
				$calificacion->update();
			}
		}
	}
}
