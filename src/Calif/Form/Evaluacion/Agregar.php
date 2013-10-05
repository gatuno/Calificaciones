<?php

class Calif_Form_Evaluacion_Agregar extends Gatuf_Form {
	public function initFields($extra=array()) {
		/* Recuperar todos los grupos de evaluación */
		$grupos = Gatuf::factory ('Calif_GrupoEvaluacion')->getList ();
		
		$choices = array ();
		foreach ($grupos as $grupo) {
			$choices [$grupo->descripcion] = $grupo->id;
		}
		
		$this->fields['grupoeval'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Grupo de evaluacion',
				'initial' => '',
				'help_text' => 'En que modalidad aplica la evaluación',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Evaluacion',
				'initial' => '',
				'help_text' => 'El nombre de su forma de evaluación',
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
		
		$grupo = new Calif_GrupoEvaluacion ($this->cleaned_data['grupoeval']);
		
		$evaluacion = new Calif_Evaluacion ();
		
		$evaluacion->grupo = $grupo;
		$evaluacion->descripcion = $this->cleaned_data['descripcion'];
		
		if ($commit) $evaluacion->create();
		
		return $evaluacion;
	}
}
