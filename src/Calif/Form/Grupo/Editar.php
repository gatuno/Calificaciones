<?php

class Calif_Form_Grupo_Crear extends Gatuf_Form {

	public function initFields($extra=array()) {
	$grupo = extra['grupo'];
	$permisos_todos = array();// Cargar desde base de datos ( tabla permisos )
	$grupo_permisos = array(); // Info de tabla gatuf_group_gatuf_permission_assoc ( dode grupo = $grupo_id )
		
		foreach ($permisos_todos as $p) { // Suponiendo que se guardan con code_name con este formato: "alumno.agregar"
			$objeto = strstr($p->name, '.', true);
			$accion = strstr($p->name, '.');
			$permisos[$objeto][$accion] = $p;
		}
		foreach($permisos as $obj => $acc){
			// Agregar label con nombre de la clase ( $obj );
			foreach($acc as $name => $a){
				if(array_key_exist( $a->id, $grupo_permisos ) )
					$activo = true;
				else 	$activo = false;

				$this->fields[$a->code_name] = new Gatuf_Form_Field_Boolean (
				array (
					'required' => true,
					'label' => $name, // ó $a->name en caso de no agregar label con nombre de la clase 
					'initial' => $activo,
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
