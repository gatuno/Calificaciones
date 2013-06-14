<?php

class Calif_Form_Materia_Agregar extends Gatuf_Form {
	public function initFields($extra=array()) {
		$departamentos = Gatuf::factory('Calif_Departamento')->getList ();
		
		$choices = array ();
		foreach ($departamentos as $departamento) {
			$choices[$departamento->departamento] = $departamento->clave;
		}
		
		$this->fields['departamento'] = new Gatuf_Form_Field_Varchar (
			array(
				'required' => true,
				'label' => 'Departamento',
				'initial' => '',
				'help_text' => 'El departamento al que pertenece la materia',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));
		
		$this->fields['clave'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Clave',
				'initial' => '',
				'help_text' => 'La clave de la materia',
				'max_length' => 5,
				'min_length' => 5,
				'widget_attrs' => array(
					'maxlength' => 5,
				),
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Materia',
				'initial' => '',
				'help_text' => 'El nombre completo de la materia',
				'max_length' => 100,
				'widget_attrs' => array(
					'maxlength' => 100,
					'size' => 30,
				),
		));
	}
	
	public function clean_clave () {
		$clave = mb_strtoupper($this->cleaned_data['clave']);
		
		if (!preg_match ("/^[a-zA-Z]+\d+$/", $clave)) {
			throw new Gatuf_Form_Invalid('La clave de la materia deben ser de 1 a 4 letras seguidas de números.');
		}
		
		$sql = new Gatuf_SQL('Clave=%s', array($clave));
        $l = Gatuf::factory('Calif_Materia')->getList(array('filter'=>$sql->gen(),'count' => true));
        if ($l > 0) {
            throw new Gatuf_Form_Invalid('Esta materia ya existe');
        }
        
        return $clave;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$materia = new Calif_Materia ();
		
		$materia->clave = $this->cleaned_data['clave'];
		$materia->descripcion = $this->cleaned_data['descripcion'];
		$materia->departamento = $this->cleaned_data['departamento'];
		
		$materia->create();
		
		return $materia;
	}
}
