<?php

class Calif_Form_Evaluacion_EvaluarAlumno extends Gatuf_Form {
	public $nrc, $codigo;
	public $gruposeval, $porcentajes;
	public function initFields($extra=array()) {
		$this->nrc = $extra['nrc'];
		$this->codigo = $extra['codigo'];
		$this->gruposeval = $extra['evaluacion']['eval'];
		$this->porcentajes = $extra['evaluacion']['porcentajes'];
		$calificacion = $extra['calificacion'];
		foreach ($this->gruposeval as $key => $descrip) {
				$this->fields[$key] = new Gatuf_Form_Field_Varchar(
				array(
				'label' => $descrip,
				'initial' => $calificacion [$key],
				'max_length' => 6,
				'widget_attrs' => array(
					'maxlength' => 6,
					),
				));
			}
		}
		
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}	
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
