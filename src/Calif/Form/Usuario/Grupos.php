<?php
class Calif_Form_Usuario_Grupos extends Gatuf_Form {
	private $user;
	public function initFields ( $extra = array () ) {
		$this->user = $extra['user'];
		
		$groups = $this->user->get_groups_list ();
		$ids = array ();
		foreach ($groups as $group) {
			$ids[] = $group->id;
		}
		
		/* Preparar la lista de grupos que el usuario no tiene */
		$groups = Gatuf::factory ('Gatuf_group')->getList (array ('order' => array ('name ASC')));
		
		$choices = array ();
		foreach ($groups as $grupo) {
			if (in_array ($grupo->id, $ids)) continue;
			$choices[$grupo->name] = $grupo->id;
		}
		
		$this->fields['grupo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Grupo',
				'help_text' => 'El nuevo grupo para este usuario',
				'initial' => '',
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
		
		$grupo = new Gatuf_Group ($this->cleaned_data['grupo']);
		
		if ($commit) {
			$this->user->setAssoc($grupo);
		}
	}
}
