<?php
class Calif_Form_Usuario_Permisos extends Gatuf_Form {
	public $usuario;
	public function initFields ( $extra = array () ) {
		$permisos_codigo = $extra['perm'];
		//$this->usuario = $extra['usuario'];
		$gperm = new Gatuf_Permission ();
		$permisos_todos = $gperm->getList( );
		/*foreach($permisos_todos as $per){
			$this->fields[$per->id] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => $per->name,
				'initial' => (in_array($per->code_name, $permisos_codigo))?true:false,
				'help_text' => $per->description
		));
		}*/
		foreach ($permisos_todos as $per) {
			$choices[$per->id . '-' .$per->name] = $per->id;
		}
			$this->fields['permiso'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Permiso:',
				'initial' => $per->name,//(in_array($per->code_name, $permisos_codigo))?true:false,
				'help_text' => 'Permite alterar y crear secciones de la carrera',
				'widget_attrs' => array (
				'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));
		
		
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
	/*	$usuario = new Calif_Usuario ();
		var_dump($usuario=$this->usuario);
		$permiso = new Gatuf_Permission ($this->cleaned_data['permiso']);
		var_dump($permiso);
		
		
		if ($commit) 
		$materia->setAssoc($carrera);
		
		return $materia;*/
		return true;
		}
}
