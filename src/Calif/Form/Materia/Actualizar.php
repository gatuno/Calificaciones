<?php

class Calif_Form_Materia_Actualizar extends Gatuf_Form {
	public $materia;
	
	public function initFields($extra=array()) {
		$this->materia = $extra['materia'];
		
		$departamentos = Gatuf::factory('Calif_Departamento')->getList ();
		
		$choices = array ();
		foreach ($departamentos as $departamento) {
			$choices[$departamento->descripcion] = $departamento->clave;
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
		
		$this->fields['creditos'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Créditos',
				'initial' => $this->materia->creditos,
				'help_text' => 'La cantidad de créditos de la materia',
				'min' => 0,
		));
		
		$this->fields['curso'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Curso',
				'initial' => $this->materia->curso,
				'help_text' => '¿La materia es un curso?'
		));
		
		$this->fields['taller'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Taller',
				'initial' => $this->materia->taller,
				'help_text' => '¿La materia es un taller?'
		));
		
		$this->fields['laboratorio'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Laboratorio',
				'initial' => $this->materia->laboratorio,
				'help_text' => '¿La materia es un laboratorio?'
		));
		
		$this->fields['seminario'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Seminario',
				'initial' => $this->materia->seminario,
				'help_text' => '¿La materia es un seminario?'
		));
		
		$this->fields['teoria'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Horas teoria',
				'initial' => $this->materia->teoria,
				'help_text' => 'La cantidad de horas teoria de esta materia',
				'min' => 0,
		));
		
		$this->fields['practica'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Horas práctica',
				'initial' => $this->materia->practica,
				'help_text' => 'La cantidad de horas práctica de esta materia',
				'min' => 0,
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		foreach (array ('teoria', 'practica') as $hora) {
			
			if ($this->materia->$hora != $this->cleaned_data[$hora]) {
				/* Disparar la señal */
				$this->materia->$hora = $this->cleaned_data[$hora];
				$params = array ('materia' => $this->materia, 'tipo' => $hora);
				
				Gatuf_Signal::send ('Calif_Materia::horasUpdated', 'Calif_Form_Materia_Actualizar', $params);
			}
		}
		$this->materia->setFromFormData ($this->cleaned_data);
		$this->materia->update();
		
		return $this->materia;
	}
}
