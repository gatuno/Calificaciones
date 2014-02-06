<?php

class Calif_Form_Materia_Agregar extends Gatuf_Form {
	public function initFields($extra=array()) {
		$departamentos = Gatuf::factory('Calif_Departamento')->getList ();
		
		$choices = array ();
		foreach ($departamentos as $departamento) {
			$choices[$departamento->descripcion] = $departamento->clave;
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
		
		$this->fields['creditos'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Créditos',
				'initial' => 0,
				'help_text' => 'La cantidad de créditos de la materia',
				'min' => 0,
		));
		
		$this->fields['curso'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Curso',
				'initial' => false,
				'help_text' => '¿La materia es un curso?'
		));
		
		$this->fields['taller'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Taller',
				'initial' => false,
				'help_text' => '¿La materia es un taller?'
		));
		
		$this->fields['laboratorio'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Laboratorio',
				'initial' => false,
				'help_text' => '¿La materia es un laboratorio?'
		));
		
		$this->fields['seminario'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Seminario',
				'initial' => false,
				'help_text' => '¿La materia es un seminario?'
		));
		
		$this->fields['teoria'] = new Gatuf_Form_Field_Float (
			array (
				'required' => true,
				'label' => 'Horas teoria',
				'initial' => 0,
				'help_text' => 'La cantidad de horas teoria de esta materia',
				'min_value' => 0.0,
		));
		
		$this->fields['practica'] = new Gatuf_Form_Field_Float (
			array (
				'required' => true,
				'label' => 'Horas práctica',
				'initial' => 0,
				'help_text' => 'La cantidad de horas práctica de esta materia',
				'min_value' => 0.0,
		));
	}
	
	public function clean_clave () {
		$clave = mb_strtoupper($this->cleaned_data['clave']);
		
		if (!preg_match ("/^[a-zA-Z]+\d+$/", $clave)) {
			throw new Gatuf_Form_Invalid('La clave de la materia deben ser de 1 a 4 letras seguidas de números.');
		}
		
		$sql = new Gatuf_SQL('clave=%s', array($clave));
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
		
		$materia->setFromFormData ($this->cleaned_data);
		if ($commit) $materia->create();
		
		return $materia;
	}
}
