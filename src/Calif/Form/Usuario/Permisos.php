<?php
class Calif_Form_Usuario_Permisos extends Gatuf_Form {

	public function initFields ( $extra = array () ) {
		$permisos_codigo = $extra['perm'];
		$gperm = new Gatuf_Permission ();
		$permisos_todos = $gperm->getList( );
		foreach($permisos_todos as $per){
			$this->fields[$per->id] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => $per->name,
				'initial' => (in_array($per->code_name, $permisos_codigo))?true:false,
				'help_text' => $per->description
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
