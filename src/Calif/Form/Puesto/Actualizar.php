<?php

class Calif_Form_Puesto_Actualizar extends Gatuf_Form {
	private $puesto;
	
	public function initFields ($extra = array ()) {
		$this->puesto = $extra['puesto'];
		
		$nrc = new Calif_Seccion ($this->puesto->nrc);
		$maestro = new Calif_Maestro ($nrc->maestro);
		
		if ($maestro->nombramiento == null) {
			$choices = array ('Asignatura (1001H ó 1002H)' => 'a', 'Honorífica (1004H)' => 'h');
		} else {
			$choices = array ('Sobre su tiempo completo/medio tiempo (1003H)' => 't', 'Asignatura (1001H ó 1002H)' => 'a', 'Honorífica (1004H)' => 'h');
		}
		
		$this->fields['carga'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Carga',
				'initial' => $this->puesto->carga,
				'help_text' => 'Donde se debería cargar esta hora',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				)
		));
	}
	
	public function save ($commit = true) {
		$this->puesto->carga = $this->cleaned_data ['carga'];
		
		if ($commit) $this->puesto->update ();
		
		return $this->puesto;
	}
}
