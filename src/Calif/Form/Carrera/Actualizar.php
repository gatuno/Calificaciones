<?php

class Calif_Form_Carrera_Actualizar extends Gatuf_Form {
	public $carrera;
	
	public function initFields($extra=array()) {
		$this->carrera = $extra['carrera'];
		
		$choices = array ();
		
		$divisiones = Gatuf::factory ('Calif_Division')->getList ();
		foreach ($divisiones as $division) {
			$choices[$division->nombre] = $division->id;
		}
		
		$this->fields['division'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'initial' => $this->carrera->division,
				'label' => 'División',
				'help_text' => 'La división a la que pertenece',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Descripción',
				'initial' => $this->carrera->descripcion,
				'help_text' => 'Una descripción como Ingeniería en Computación',
				'max_length' => 100,
				'widget_attrs' => array(
					'maxlength' => 100,
					'size' => 30,
				),
		));
		
		$this->fields['color'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Color',
				'help_text' => 'Un color para identificar a la carrera',
				'widget' => 'Gatuf_Form_Widget_ColorPicker',
				'initial' => '#'.str_pad (dechex ($this->carrera->color), 6, '0', STR_PAD_LEFT),
		));
	}
	
	public function clean_color () {
		$color = $this->cleaned_data['color'];
		
		if (!preg_match ('/^#[\dA-Fa-f]{6}$/', $color)) {
			throw new Gatuf_Form_Invalid ('Color inválido');
		}
		
		return hexdec (substr ($color, 1));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->carrera->descripcion = $this->cleaned_data['descripcion'];
		$this->carrera->color = $this->cleaned_data['color'];
		
		$this->carrera->update();
		
		return $this->carrera;
	}
}
