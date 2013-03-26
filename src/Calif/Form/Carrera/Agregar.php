<?php

class Calif_Form_Carrera_Agregar extends Pluf_Form {
	public function initFields($extra=array()) {
		$this->fields['clave'] = new Pluf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Clave',
				'initial' => '',
				'help_text' => 'Debe ser una clave de carrera única',
				'widget_attrs' => array(
					'maxlength' => 50,
				),
		));
		
		$this->fields['descripcion'] = new Pluf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Descripción',
				'initial' => '',
				'help_text' => 'Una descripción como Ingeniería en Computación',
				'widget_attrs' => array(
					'maxlength' => 100,
					'size' => 30,
				),
		));
	}
	
	public function clean_clave () {
		$clave = mb_strtoupper($this->cleaned_data['clave']);
		
		if (!preg_match ("/^([A-Za-z]){3,5}$/", $clave)) {
			throw new Pluf_Form_Invalid('La clave de la carrera contiene caracteres inválidos o es muy larga.');
		}
		
		$sql = new Pluf_SQL('clave=%s', array($clave));
        $l = Pluf::factory('Calif_Carrera')->getList(array('filter'=>$sql->gen()));
        if ($l->count() > 0) {
            throw new Pluf_Form_Invalid('Esta clave de carrera ya está en uso');
        }
        
        return $clave;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
            throw new Exception('Cannot save the model from an invalid form.');
        }
        
        $carrera = new Calif_Carrera ();
        
        $carrera->clave = $this->cleaned_data['clave'];
        $carrera->descripcion = $this->cleaned_data['descripcion'];
        
        $carrera->create();
        
        return $carrera;
	}
}
