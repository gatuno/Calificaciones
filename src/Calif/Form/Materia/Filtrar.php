<?php

class Calif_Form_Materia_Filtrar extends Gatuf_Form {

	public function initFields($extra=array()) {

		$choices = array ();

		$departamentos = Gatuf::factory('Calif_Departamento')->getList();
		foreach ($departamentos as $departamento) {
				$choices['Departamento'][$departamento->descripcion] = 'd_'.$departamento->clave;
		}

		$carreras = Gatuf::factory('Calif_Carrera')->getList();
		foreach ($carreras as $carrera) {
				$choices['Carrera'][$carrera->descripcion] = 'c_'.$carrera->clave;
		}
		
		$this->fields['filtro'] = new Gatuf_Form_Field_Varchar (
			array(
				'label' => 'Filtrar',
				'initial' => '',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		$filtro = $this->cleaned_data['filtro'];
		return $filtro;
	}
}
