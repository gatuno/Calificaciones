<?php
class Calif_Form_Usuario_Permisos extends Gatuf_Form {
	private $user;
	public function initFields ($extra = array ()) {
		$this->user = $extra['user'];
		
		foreach (Gatuf::factory ('Gatuf_Permission')->getList() as $per) {
			if ($this->user->hasPerm ($per->application.'-'.$per->code_name)) continue;
			$choices[$per->name] = $per->id;
		}
		
		$this->fields['permiso'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Permiso',
				'initial' => '',
				'help_text' => 'Permite alterar y crear secciones de la carrera',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save a invalid form');
		}
		
		$permiso = new Gatuf_Permission ($this->cleaned_data ['permiso']);
		
		if ($commit) {
			$this->user->setAssoc ($permiso);
		}
	}
}
