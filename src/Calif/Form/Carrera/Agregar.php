<?php

class Calif_Form_Carrera_Agregar extends Gatuf_Form {
	public function initFields($extra=array()) {
		$choices = array ();
		
		$divisiones = Gatuf::factory ('Calif_Division')->getList ();
		foreach ($divisiones as $division) {
			$choices[$division->nombre] = $division->id;
		}
		
		$this->fields['division'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'División',
				'help_text' => 'La división a la que pertenece',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		));
		
		$this->fields['clave'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Clave',
				'initial' => '',
				'help_text' => 'Debe ser una clave de carrera única',
				'max_length' => 5,
				'min_length' => 3,
				'widget_attrs' => array(
					'maxlength' => 5,
				),
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Descripción',
				'initial' => '',
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
				'initial' => '#FFFFFF',
		));
	}
	
	public function clean_color () {
		$color = $this->cleaned_data['color'];
		
		if (!preg_match ('/^#[\dA-Fa-f]{6}$/', $color)) {
			throw new Gatuf_Form_Invalid ('Color inválido');
		}
		
		return hexdec (substr ($color, 1));
	}
	
	public function clean_clave () {
		$clave = mb_strtoupper($this->cleaned_data['clave']);
		
		if (!preg_match ("/^([A-Za-z]){3,5}$/", $clave)) {
			throw new Gatuf_Form_Invalid('La clave de la carrera contiene caracteres inválidos.');
		}
		
		$sql = new Gatuf_SQL('Clave=%s', array($clave));
		$l = Gatuf::factory('Calif_Carrera')->getList(array('filter'=>$sql->gen(),'count' => true));
		if ($l > 0) {
			throw new Gatuf_Form_Invalid('Esta clave de carrera ya está en uso');
		}
		
		return $clave;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$carrera = new Calif_Carrera ();
		$carrera->setFromFormData ($this->cleaned_data);
		
		if ($commit) $carrera->create();
		
		return $carrera;
	}
}
