<?php

class Calif_Form_Seccion_Evaluar extends Gatuf_Form {
	public $nrc;
	public $modo_eval;
	public $alumnos;
	public $porcentaje;
	public $array_eval;
	
	public function initFields($extra=array()) {
		$this->nrc = $extra['nrc'];
		$this->modo_eval = $extra['modo_eval'];
		$this->porcentaje = $extra['porcentaje'];
		$this->array_eval = array(-1 => 'NP', -2 => 'SD');	
		
		$sql = new Gatuf_SQL ('evaluacion=%s AND nrc=%s', array ($this->modo_eval, $this->nrc->nrc));
		$cadena_sql_base = $sql->gen();
		
		$this->alumnos = $this->nrc->getAlumnosList ();
		$calif_model = new Calif_Calificacion ();
		
		foreach ($this->alumnos as $a) {
			
			$sql = $cadena_sql_base.sprintf (' AND alumno=%s', $a->codigo);
			$calif_model->getCalif ($sql);
			
			$valor = $calif_model->valor;
			if (array_key_exists($valor , $this->array_eval)) {
					$valor = $this->array_eval[$valor];
			}
			else 
				$valor = is_null ($valor) ? '' : $valor.'%';
			
			$this->fields['calif_'.$a->codigo] = new Gatuf_Form_Field_Varchar (
				array (
					'required' => false,
					'label' => $a->nombre.' '.$a->apellido,
					'initial' => $valor,
					'max_length' => 6,
					'widget_attrs' => array (
						'maxlength' => 6,
					),
			));
		}
	}
	
		public function clean () {
		$eval_model = new Calif_Evaluacion ();
		foreach ($this->alumnos as $alumno ) {
			$calificacion = $this->cleaned_data ['calif_'.$alumno->codigo];
			$flip_eval = array_flip ($this->array_eval);
			if ($calificacion === '') {
			$this->cleaned_data['calif_'.$alumno->codigo] =NULL;
				continue;
			}
			
			if (array_key_exists ($calificacion, $flip_eval)) {
				$this->cleaned_data['calif_'.$alumno->codigo] = $flip_eval[$calificacion];
				continue;
			}
			if (preg_match ('/^(\d+)%$/', $calificacion, $submatch)) {
				/* Es un porcentaje */
				if ($submatch[1] > 100) {
					throw new Gatuf_Form_Invalid ('El porcentaje en la calificacion del alumno "'.$alumno->nombre.'" es inválida');
				}
				$this->cleaned_data['calif_'.$alumno->codigo] = $submatch[1];
				continue;
			}
			if (preg_match ('/^(\d+)$/', $calificacion, $submatch)) {
				/* Es un valor crudo, verificar que no sobrepase el máximo porcentaje */
				if ($submatch[1] > $this->porcentaje) {
					$this->cleaned_data['calif_'.$alumno->codigo] = $this->porcentaje;
				}
				$this->cleaned_data['calif_'.$alumno->codigo] = (int) (($this->cleaned_data['calif_'.$alumno->codigo] * 100) / $this->porcentaje);
				continue;
			}

			throw new Gatuf_Form_Invalid ('La calificacion en '.$alumno->nombre.' es inválida');
		}
		
		return $this->cleaned_data;
	}
	
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		$calificacion = new Calif_Calificacion();
		
		foreach($this->alumnos as $alumno){
				$calificacion->nrc = $this->nrc->nrc;
				$calificacion->alumno = $alumno->codigo;
				$calificacion->evaluacion = $this->modo_eval;
				$calificacion->valor = $this->cleaned_data['calif_'.$alumno->codigo];
				$calificacion->update();
			}
		}
}
