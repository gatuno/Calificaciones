<?php

class Calif_Form_Seccion_Matricular extends Gatuf_Form {
	public $alumnos;
	public $seccion;
	
	public function initFields($extra=array()) {
	//Arreglar Pedir solo usuarios capaces de tomar materia ( en base a su carrera ) y evitar usuarios que ya la cursaron
		$this->seccion = $extra['nrc'];
		//$all_alumnos = Gatuf::factory('Calif_Alumno')->getList();
		$all_alumnos = $this->seccion->get_grupos_list ();
		foreach ($all_alumnos as $alumno){
			$this->alumnos[$alumno->codigo] = $alumno->nombre; 
		}
		
		$this->fields['calif_alumno'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Codigo Alumno ',
				'max_length' => 15,
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		//REPARAR: Validar que exista codigo de Alumno y la carrera del alumno acepte la materia de la seccion
		if (array_key_exists($this->cleaned_data['calif_alumno'], $this->alumnos)) {
			throw new Gatuf_Form_Invalid ('Alunmo ya registrado en la seccion');
		}
		$this->seccion->addAlumno($this->cleaned_data['calif_alumno']);
	}
}
