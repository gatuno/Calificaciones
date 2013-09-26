<?php

class Calif_Form_Alumno_Evaluar extends Gatuf_Form {
	public $nrc, $alumno;
	public $lista_porcentajes;
	public $array_eval;
	
	public function initFields($extra=array()) {
	
		$this->nrc = $extra['nrc'];
		$this->alumno = $extra['alumno'];
		$grupo_eval = $extra['grupo_eval'];
		$this->array_eval = array(-1 => 'NP', -2 => 'SD');	
		
		$sql = new Gatuf_SQL ('materia=%s AND grupo=%s', array ($this->nrc->materia, $grupo_eval->id));
		$this->lista_porcentajes = Gatuf::factory ('Calif_Porcentaje')->getList (array ('filter' => $sql->gen ()));
		
		$sql = new Gatuf_SQL ('alumno=%s AND nrc=%s', array ($this->alumno->codigo, $this->nrc->nrc));
		
		$cadena_sql_base = $sql->gen();
		$eval_model = new Calif_Evaluacion ();
		$calif_model = new Calif_Calificacion ();
		
		foreach ($this->lista_porcentajes as $p) {
			$eval_model->getEval ($p->evaluacion);
			
			$sql = $cadena_sql_base.sprintf (' AND evaluacion=%s', $p->evaluacion);
			$calif_model->getCalif ($sql);
			
			$valor = $calif_model->valor;
			if (array_key_exists($valor , $this->array_eval)) {
					$valor = $this->array_eval[$valor];
			}
			else 
				$valor = is_null ($valor) ? '' : $valor.'%';
				
			$this->fields['calif_'.$p->evaluacion] = new Gatuf_Form_Field_Varchar (
				array (
					'required' => false,
					'label' => $eval_model->descripcion,
					'initial' => $valor,
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
		$flip_eval = array_flip($this->array_eval);
		foreach($this->lista_porcentajes as $p){
			if($valor = $this->cleaned_data['calif_'.$p->evaluacion]){
				if (array_key_exists($valor, $flip_eval)) {
					$valor = $flip_eval[$valor];
			}
				else if(!$pos = strpos($valor, '%')){
					$valor = ($valor*100)/$p->porcentaje;
				}
				$calificacion = new Calif_Calificacion();
				$calificacion->nrc = $this->nrc->nrc;
				$calificacion->alumno = $this->alumno->codigo;
				$calificacion->evaluacion = $p->evaluacion;
				$calificacion->valor = $valor;
				$calificacion->update();
			}
		}
	}
}
