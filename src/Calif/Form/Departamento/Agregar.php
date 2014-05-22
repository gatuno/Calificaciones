<?php

class Calif_Form_Departamento_Agregar extends Gatuf_Form {

	public function initFields($extra=array()) {
		
		$this->fields['clave'] = new Gatuf_Form_Field_Integer (
			array(
				'required' => true,
				'label' => 'Clave',
				'initial' => '',
				'help_text' => 'La clave del departamento',
				'min' => 0,
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Departamento',
				'initial' => '',
				'help_text' => 'El nombre completo del departamento',
				'max_length' => 100,
				'widget_attrs' => array(
					'maxlength' => 100,
					'size' => 30,
				),
		));
	}
	
	public function clean_clave () {
		$clave = $this->cleaned_data['clave'];
		
		$sql = new Gatuf_SQL('clave=%s', array($clave));
        $l = Gatuf::factory('Calif_Departamento')->getList(array('filter'=>$sql->gen(),'count' => true));
        if ($l > 0) {
            throw new Gatuf_Form_Invalid('Este departamento ya existe');
        }
        
        return $clave;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$departamento = new Calif_Departamento ();
		
		$departamento->setFromFormData ($this->cleaned_data);
		if ($commit) $departamento->create();
		
		return $departamento;
	}
}
