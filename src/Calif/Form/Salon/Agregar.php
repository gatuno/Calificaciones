<?php

class Calif_Form_Salon_Agregar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		/* Preparar la lista de edificios */
		$edificios = Gatuf::factory ('Calif_Edificio')->getList (array ('order' => array ('clave ASC')));

		$choices = array ();
		foreach ($edificios as $edificio) {
			$choices[$edificio->clave.' - '.$edificio->descripcion] = $edificio->clave;
		}
		
		$this->fields['edificio'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Edificio',
				'help_text' => 'El edificio donde se encuentra en nuevo salon',
				'initial' => '',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));

		$this->fields['aula'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Aula',
				'help_text' => 'La clave del aula estilo Siiau',
				'initial' => '',
				'max_length' => 10,
				'min_length' => 1,
				'widget_attrs' => array (
					'maxlength' => 10,
					'size' => 30,
				)
		));

		$this->fields['cupo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Cupo',
				'help_text' => 'El cupo de esta aula',
				'initial' => 40,
				'min' => 0
		));
	}
	
	function clean () {
		$aula = $this->cleaned_data['aula'];
		$edificio = $this->cleaned_data['edificio'];

		$salon = new Calif_Salon ();
		if (false !== $salon->getSalon ($edificio, $aula)) {
			/* Este salon ya existe */
			throw new Gatuf_Form_Invalid ('Este salon ya existe');
		}

		return $this->cleaned_data;
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$salon = new Calif_Salon ();
		$salon->edificio = $this->cleaned_data['edificio'];
		$salon->aula = $this->cleaned_data['aula'];
		$salon->cupo = $this->cleaned_data['cupo'];

		$salon->create ();

		return $salon;
	}
}
