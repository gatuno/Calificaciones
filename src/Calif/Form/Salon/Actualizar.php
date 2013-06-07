<?php

class Calif_Form_Salon_Actualizar extends Gatuf_Form {
	public $salon;
	
	public function initFields ($extra = array ()) {
		$this->salon = $extra['salon'];
		
		$edificio = new Calif_Edificio ();
		$edificio->getEdificio ($this->salon->edificio);
		
		$this->fields['edificio'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Edificio',
				'help_text' => 'El edificio donde se encuentra en nuevo salon',
				'initial' => $edificio->descripcion,
				'widget_attrs' => array (
					'readonly' => 'readonly',
				)
		));

		$this->fields['aula'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Aula',
				'help_text' => 'La clave del aula estilo Siiau',
				'initial' => $this->salon->aula,
				'max_length' => 10,
				'min_length' => 1,
				'widget_attrs' => array (
					'maxlength' => 10,
					'readonly' => 'readonly',
					'size' => 30,
				)
		));

		$this->fields['cupo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Cupo',
				'help_text' => 'El cupo de esta aula',
				'initial' => $this->salon->cupo,
				'min' => 0
		));
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->salon->cupo = $this->cleaned_data['cupo'];

		$this->salon->update ();

		return $this->salon;
	}
}
