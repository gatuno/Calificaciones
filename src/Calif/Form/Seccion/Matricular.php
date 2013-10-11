<?php

class Calif_Form_Seccion_Matricular extends Gatuf_Form {
	public $alumnos;
	
	public function initFields($extra=array()) {
	//Arreglar Pedir solo usuarios capaces de tomar materia ( en base a su carrera ) y evitar usuarios que ya la cursaron
		$all_alumnos = Gatuf::factory('Calif_Alumno')->getList();
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
		}
}
