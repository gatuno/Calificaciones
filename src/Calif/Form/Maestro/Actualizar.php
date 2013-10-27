<?php

class Calif_Form_Maestro_Actualizar extends Gatuf_Form {
	public $maestro;
	
	public function initFields ($extra = array ()) {
		$this->maestro = $extra['maestro'];
		
		/* Preparar catalogos */
		$choices_grados = array ('Lic.' => 'L', 'Ing.' => 'I', 'Mtro./Mtra' => 'M', 'Dr./Dra.' => 'D');
		$choices_sex = array ('Masculino' => 'M', 'Femenino' => 'F');
		
		$choices_nombramientos = array ();
		foreach (Gatuf::factory ('Calif_Nombramiento')->getList () as $nomb) {
			$choices_nombramientos[$nomb->descripcion] = $nomb->clave;
		}
		
		if (isset ($choices_nombramientos['1003H'])) unset ($choices_nombramientos['1003H']);
		if (isset ($choices_nombramientos['1004H'])) unset ($choices_nombramientos['1004H']);
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => $this->maestro->nombre,
				'help_text' => 'El nombre o nombres del profesor',
				'max_length' => 50,
				'widget_attrs' => array (
					'maxlength' => 50,
					'size' => 30,
				),
		));
		
		$this->fields['apellido'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Apellido',
				'initial' => $this->maestro->apellido,
				'help_text' => 'Los apellidos del profesor',
				'max_length' => 100,
				'widget_attrs' => array (
					'maxlength' => 100,
					'size' => 30,
				),
		));
		
		$this->fields['sexo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Sexo',
				'initial' => $this->maestro->sexo,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_sex,
				),
		));
		
		$this->fields['grado'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Grado de estudios',
				'initial' => $this->maestro->grado,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_grados,
				),
		));
		
		$this->fields['correo'] = new Gatuf_Form_Field_Email (
			array (
				'required' => true,
				'label' => 'Correo',
				'initial' => $this->maestro->user->email,
				'help_text' => 'Un correo',
		));
		
		$this->fields['nombramiento'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombramiento',
				'initial' => $this->maestro->nombramiento,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_nombramientos,
				),
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the model from and invalid form.');
		}
		
		$this->maestro->setFromFormData ($this->cleaned_data);
		$this->maestro->user->email = $this->cleaned_data['correo'];
		
		if ($commit) {
			$this->maestro->update();
			$this->maestro->user->update ();
		}
		return $this->maestro;
	}
}
