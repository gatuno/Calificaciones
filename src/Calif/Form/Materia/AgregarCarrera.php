<?php

class Calif_Form_Materia_AgregarCarrera extends Gatuf_Form {
	public $materia;
	public function initFields($extra=array()) {
		$this->materia = $extra['materia'];
		$carreras = Gatuf::factory('Calif_Carrera')->getList ();
		$choices = array ();
		foreach ($carreras as $carrera) {
			$choices[$carrera->descripcion] = $carrera->clave;
		}
		
		$this->fields['carrera'] = new Gatuf_Form_Field_Varchar (
			array(
				'required' => true,
				'label' => 'Carrera',
				'initial' => '',
				'help_text' => 'Elige la carrera a la que la materia pertenece',
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
		$materia = new Calif_Materia ();
		$materia=$this->materia;
		$carrera = new Calif_Carrera ($this->cleaned_data['carrera']);
		
		
		
		if ($commit) 
		$materia->setAssoc($carrera);
		
		return $materia;
	}
}
