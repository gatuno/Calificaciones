<?php

class Calif_Form_Reportes_Oferta_SeleccionarNoPorDepartamento extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$choices = array ();
		
		foreach (Gatuf::factory ('Calif_Departamento')->getList () as $departamento) {
			$choices[$departamento->descripcion] = $departamento->clave;
		}
		
		$this->fields['departamento'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Departamento',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		)); 
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the form in a valid state');
		}
		
		$departamento = new Calif_Departamento ($this->cleaned_data['departamento']);
		
		return $departamento;
	}
}
