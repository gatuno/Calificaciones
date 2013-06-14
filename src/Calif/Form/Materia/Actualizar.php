<?php

class Calif_Form_Materia_Actualizar extends Gatuf_Form {
	public $materia;
	
	public function initFields($extra=array()) {
		$this->materia = $extra['materia'];
		
		$departamentos = Gatuf::factory('Calif_Departamento')->getList ();
		
		$choices = array ();
		foreach ($departamentos as $departamento) {
			$choices[$departamento->departamento] = $departamento->clave;
		}
		
		$this->fields['departamento'] = new Gatuf_Form_Field_Varchar (
			array(
				'required' => true,
				'label' => 'Departamento',
				'initial' => $this->materia->departamento,
				'help_text' => 'El departamento al que pertenece la materia',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));
		
		$this->fields['clave'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => false,
				'label' => 'Clave',
				'initial' => $this->materia->clave,
				'help_text' => 'La clave de la materia',
				'max_length' => 5,
				'min_length' => 5,
				'widget_attrs' => array(
					'maxlength' => 5,
					'readonly' => 'readonly',
				),
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Materia',
				'initial' => $this->materia->descripcion,
				'help_text' => 'El nombre completo de la materia',
				'max_length' => 100,
				'widget_attrs' => array(
					'maxlength' => 100,
					'size' => 30,
				),
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->materia->descripcion = $this->cleaned_data['descripcion'];
		$this->materia->departamento = $this->cleaned_data['departamento'];
		
		$this->materia->update();
		
		return $this->materia;
	}
}
