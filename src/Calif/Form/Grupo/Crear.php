<?php

class Calif_Form_Grupo_Crear extends Gatuf_Form {

	public function initFields($extra=array()) {
	$permisos_todos = array();// Cargar desde base de datos ( tabla permisos )
	
	$this->fields['name'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Nombre',
				'initial' => '',
				'help_text' => 'El nombre del Grupo',
				'max_length' => 5,
				'min_length' => 5,
				'widget_attrs' => array(
					'maxlength' => 5,
				),
		));
		
		$this->fields['description'] = new Pluf_Form_Field_Varchar(
                                      array('required' => true,
                                            'label' => 'Descripcion',
                                            'widget' => 'Pluf_Form_Widget_TextareaInput',
                                            'widget_attrs' => array('rows' => 3,
                                                                'cols' => 75),
     ));
		
		foreach ($permisos_todos as $p) { // Suponiendo que se guardan con code_name con este formato: "alumno.agregar"
			$objeto = strstr($p->name, '.', true);
			$accion = strstr($p->name, '.');
			$permisos[$objeto][$accion] = $p;
		}
		foreach($permisos as $obj => $acc){
			// Agregar label con nombre de la clase ( $obj );
			foreach($acc as $name => $a){

				$this->fields[$a->code_name] = new Gatuf_Form_Field_Boolean (
				array (
					'required' => true,
					'label' => $name, // ó $a->name en caso de no agregar label con nombre de la clase 
					'initial' => false,
				));
			}
		}
		
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
			return true;
		}
}
