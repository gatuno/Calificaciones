<?php
class Calif_Form_Usuario_Permisos extends Gatuf_Form {
	public $usr;
	public function initFields ( $extra = array () ) {
		$permisos_codigo = $extra['perm'];
		
		$gperm = new Gatuf_Permission ();
		$permisos_todos = $gperm->getList( );
		foreach ($permisos_todos as $per) {
			$choices[$per->id . ' - ' .$per->name] = $per->id;
		}
			$this->fields['permiso'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Permiso:',
				'initial' => $per->id,//(in_array($per->code_name, $permisos_codigo))?true:false,
				'help_text' => 'Permite alterar y crear secciones de la carrera',
				'widget_attrs' => array (
				'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));
	}
}
