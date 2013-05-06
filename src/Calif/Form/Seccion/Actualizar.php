<?php

class Calif_Form_Seccion_Actualizar extends Gatuf_Form {
	public $seccion;
	
	public function initFields($extra=array()) {
		$this->seccion = $extra['seccion'];
		
		$this->fields['nrc'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => false,
				'label' => 'NRC',
				'initial' => $this->seccion->nrc,
				'help_text' => 'El Nrc del grupo',
				'widget_attrs' => array(
					'readonly' => 'readonly',
				),
		));
		
		$this->fields['materia'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => false,
				'label' => 'Materia',
				'initial' => $this->seccion->materia,
				'help_text' => 'El nombre completo de la materia',
				'widget_attrs' => array(
					'readonly' => 'readonly',
				),
		));
		
		$this->fields['seccion'] = new Gatuf_Form_Field_Varchar(
			array (
				'required' => false,
				'label' => 'Seccion',
				'initial' => $this->seccion->seccion,
				'help_text' => 'La sección, como D01 o D23',
				'widget_attrs' => array(
					'readonly' => 'readonly',
				)
		));
		
		$todoslosmaestros = Gatuf::factory ('Calif_Maestro')->getList (
		                    array ('order' => array ('Apellido ASC', 'Nombre ASC')));
		$choices = array ();
		foreach ($todoslosmaestros as $m) {
			$choices[$m->apellido . ' ' . $m->nombre] = $m->codigo;
		}
		
		$this->fields['maestro'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Maestro',
				'initial' => $this->seccion->maestro,
				'help_text' => 'El maestro de este grupo',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->seccion->maestro = $this->cleaned_data['maestro'];
		
		$this->seccion->update();
		
		return $this->seccion;
	}
}
