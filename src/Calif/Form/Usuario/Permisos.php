<?php
class Calif_Form_Usuario_Permisos extends Gatuf_Form {
	//public $usr;
	public function initFields ( $extra = array () ) {
		$permisos_codigo = $extra['perm'];
	//	$this->usr = $extra['usr'];
		$gperm = new Gatuf_Permission ();
		$permisos_todos = $gperm->getList( );
		foreach ($permisos_todos as $per) {
			$choices[$per->id . ' - ' .$per->name] = $per->id;
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
		
		/* Preparar la lista de grupos 
		$grupos = Gatuf::factory ('Gatuf_group')->getList (array ('order' => array ('id ASC')));

		$choices = array ();
		foreach ($grupos as $grupo) {
			$choices[$grupo->id.' - '.$grupo->name] = $grupo->id;
		}

		$this->fields['grupo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Grupos:',
				'help_text' => 'Grupo al cuál se le asignará',
				'initial' => '',
				'widget_attrs' => array (
				'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));*/
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		/*$usr = new Calif_Usuario ();
		var_dump($user=$this->usr);
		$permiso = new Gatuf_Permission ($this->cleaned_data['permiso']);
		var_dump($permiso);
		
		
		if ($commit) 
		$usr->setAssoc($permiso);
		
		return $usr;*/
		return true;
		}
}
