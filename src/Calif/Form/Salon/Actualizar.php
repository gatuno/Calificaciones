<?php

class Calif_Form_Salon_Actualizar extends Gatuf_Form {
	public $salon;
	
	public function initFields ($extra = array ()) {
		$this->salon = $extra['salon'];

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
