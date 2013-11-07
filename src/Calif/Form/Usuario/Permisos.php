<?php

class Calif_Form_Usuario_Permisos extends Gatuf_Form {

	public function initFields($extra=array()) {
	$usuario = $extra[' usuario'];
	$grupos_usuario = array(); // Info de tabla calif_users_gatuf_group_assoc ( dode usuario = $usuario_codigo )
	$grupos = array() // tabla groups
	$permisos_usuario = array(); // Info de tabla calif_users_gatuf_permission_assoc ( dode usuario = $usuario_codigo )
	$permisos_todos = array();// Cargar desde base de datos ( tabla permisos )
		
		foreach ($permisos_todos as $p) { // Suponiendo que se guardan con code_name con este formato: "alumno.agregar"
			$objeto = strstr($p->name, '.', true);
			$accion = strstr($p->name, '.');
			$permisos[$objeto][$accion] = $p;
		}
		
		foreach($permisos as $obj => $acc){
			// Agregar label con nombre de la clase ( $obj );
			foreach($acc as $name => $a){
				if(array_key_exist( $a->id, $permisos_usuario ) )
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
		
			foreach($grupos as $g ){
				if(array_key_exist( $g->id, $grupos_usuario ) )
					$activo = true;
				else 	$activo = false;

				$this->fields[$g->name] = new Gatuf_Form_Field_Boolean (
				array (
					'required' => true,
					'label' => $g->name, // ó $a->name en caso de no agregar label con nombre de la clase 
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
