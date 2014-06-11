<?php

class Calif_Form_Seccion_Filtrar extends Gatuf_Form {

	public function initFields($extra=array()) {

		$choices = array ();

		$divisiones = Gatuf::factory('Calif_Division')->getList();
		foreach ($divisiones as $division) {
				$choices['Divisiones'][$division->descripcion] = 'i_'.$division->id;
		}
		
		$carreras = Gatuf::factory('Calif_Carrera')->getList();
		foreach ($carreras as $carrera) {
				$choices['Carreras'][$carrera->descripcion] = 'c_'.$carrera->clave;
		}

		$departamentos = Gatuf::factory('Calif_Departamento')->getList();
		foreach ($departamentos as $departamento) {
				$choices['Departamentos'][$departamento->descripcion] = 'd_'.$departamento->clave;
		}

		$this->fields['filtro'] = new Gatuf_Form_Field_Varchar (
			array(
				'label' => 'Filtrar secciones de ',
				'initial' =>'',
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
