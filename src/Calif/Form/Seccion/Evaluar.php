<?php

class Calif_Form_Seccion_Evaluar extends Gatuf_Form {
	public $nrc;
	public $modo_eval;
	public $alumnos;
	public $porcentaje;
	
	public function initFields($extra=array()) {
		$this->nrc = $extra['nrc'];
		$this->modo_eval = $extra['modo_eval'];
		$this->porcentaje = $extra['porcentaje'];
		
		$sql = new Gatuf_SQL ('evaluacion=%s AND nrc=%s', array ($this->modo_eval, $this->nrc->nrc));
		$cadena_sql_base = $sql->gen();
		
		$this->alumnos = $this->nrc->getAlumnosList ();
		$calif_model = new Calif_Calificacion ();
		
		foreach ($this->alumnos as $a) {
			
			$sql = $cadena_sql_base.sprintf (' AND alumno=%s', $a->codigo);
			$calif_model->getCalif ($sql);
			
			$this->fields[$a->codigo] = new Gatuf_Form_Field_Varchar (
				array (
					'required' => false,
					'label' => $a->nombre.' '.$a->apellido,
					'initial' => is_null ($calif_model->valor) ? '' : $calif_model->valor.'%',
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
		
		foreach($this->alumnos as $key => $alumno){
			$valor = $this->cleaned_data[$alumno->codigo];
			if($valor){
				if(!$pos = strpos($valor, '%') ){
					$valor = ($valor*100)/$this->porcentaje;
				}
				$calificacion = new Calif_Calificacion();
				$calificacion->nrc = $this->nrc->nrc;
				$calificacion->alumno = $alumno->codigo;
				$calificacion->evaluacion = $this->modo_eval;
				$calificacion->valor = $valor;
				$calificacion->update();
			}
		}
	}
}
