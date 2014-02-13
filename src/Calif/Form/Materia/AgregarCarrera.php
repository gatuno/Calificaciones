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
	$this->fields['clave'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Clave',
				'initial' => '',
				'help_text' => 'La clave de la materia',
				'max_length' => 5,
				'min_length' => 5,
				'widget_attrs' => array(
					'maxlength' => 5,
				),
		));
		
		
	}
	
	/*public function clean_clave () {
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
	}*/
	//Queda pendiente capturarlo a la asociación materia_carrera
	/*public function save ($commit=true) {
		/*if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}*
		
		$materia = new Calif_Materia ();
		$carrera = new Calif_Carrera();

		$materia->get('CC100');//$this->cleaned_data['clave'];
		//$materia->get_carreras_list();
	//	$carrera->get_materia_list();
	//	$carrera->carrera=$this->cleaned_data['carrera'];
		
		if ($commit) $materia->setAssoc('BIM');
		//var_dump($carrera->setAssoc($materia));
	
	//	if ($commit) $materia->create();
		
		return $materia;
	}*/
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		$materia = new Calif_Materia ($this->cleaned_data['clave']);
	//	$materia=new Calif_Materia();
		//var_dump($materia->clave=$this->cleaned_data['materia']);
		$carrera = new Calif_Carrera ($this->cleaned_data['carrera']);
		
		
	    //$carrera->get($this->cleaned_data['clave']);
		//$materia->carrera=$this->cleaned_data['clave'];
		//$carrera->clave=$this->cleaned_data['carrera'];
	//	$carrera->get($this->cleaned_data['carrera']);
	//	$materia->get($this->cleaned_data['clave']);
		
		
		
		if ($commit) 
		$materia->setAssoc($carrera);
	//	$carrera->setAssoc($materia);
		//var_dump($carrera->setAssoc($materia));
	
	//	if ($commit) $materia->create();
		
		return $materia;
	}
}
