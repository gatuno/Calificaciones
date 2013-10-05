<?php

class Calif_Form_Materia_AgregarEval extends Gatuf_Form {
	public $materia;
	
	public function initFields($extra=array()) {
		$choices = array ();
		$this->materia = $extra['materia'];
		
		foreach ($this->materia->getNotEvals ($extra['gp']) as $eval) {
			$choices [$eval->descripcion] = $eval->id;
		}
		
		$this->fields['eval'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Forma de evaluacion',
				'initial' => '',
				'help_text' => 'La forma de evaluación que aplica sobre esta materia',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['porcentaje'] = new Gatuf_Form_Field_Integer (
			array(
				'required' => true,
				'label' => 'Porcentaje',
				'initial' => '20',
				'help_text' => 'La ponderación que recibe esta forma de evaluación',
				'min' => 1,
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$evaluacion = new Calif_Evaluacion ();
		$evaluacion->get ($this->cleaned_data['eval']);
		
		$porcentaje = new Calif_Porcentaje ();
		$porcentaje->materia = $this->materia;
		$porcentaje->porcentaje = $this->cleaned_data['porcentaje'];
		$porcentaje->evaluacion = $evaluacion;
		
		$porcentaje->create ();
		
		return true;
	}
}

