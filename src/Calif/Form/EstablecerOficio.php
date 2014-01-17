<?php

class Calif_Form_EstablecerOficio extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		if (isset ($extra['numero'])) $inicio = $extra['numero'];
		else $inicio = 1;
		$this->fields['numero'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'initial' => $inicio,
				'min' => 1,
				'label' => 'Número de oficio',
				'help_text' => 'Numero de oficio inicial',
				'widget_attrs' => array (
					'size' => 5,
				),
		));
	}
	
	public function save ($commit = true) {
		return $this->cleaned_data ['numero'];
	}
}
