<?php

class Calif_Form_Materia_AgregarCarrera extends Gatuf_Form {
	public function initFields($extra=array()) {
		$carreras = Gatuf::factory('Calif_Carrera')->getList ();
		
		$choices = array ();
		foreach ($carreras as $carrera) {
			$choices[$carrera->descripcion] = $carrera->clave;
		}
		
		$this->fields['carrera'] = new Gatuf_Form_Field_Varchar (
			array(
				'required' => true,
				'label' => 'Carrera',
				'initial' => '',
				'help_text' => 'Elige la carrera a la que la materia pertenece',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));
	
		
	}
	
	public function clean_clave () {
		$clave = mb_strtoupper($this->cleaned_data['clave']);
		
		if (!preg_match ("/^[a-zA-Z]+\d+$/", $clave)) {
			throw new Gatuf_Form_Invalid('La clave de la materia deben ser de 1 a 4 letras seguidas de números.');
		}
		
		$sql = new Gatuf_SQL('clave=%s', array($clave));
        $l = Gatuf::factory('Calif_Materia')->getList(array('filter'=>$sql->gen(),'count' => true));
        if ($l > 0) {
            throw new Gatuf_Form_Invalid('Esta materia ya existe');
        }
        
        return $clave;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$materia = new Calif_Materia ();
		
		$materia->setFromFormData ($this->cleaned_data);
		if ($commit) $materia->create();
		
		return $materia;
	}
}
