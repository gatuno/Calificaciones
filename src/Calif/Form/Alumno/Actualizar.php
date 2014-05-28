<?php

class Calif_Form_Alumno_Actualizar extends Gatuf_Form {
	public $alumno;

	public function initFields ($extra = array ()) {

		$this->alumno = $extra['alumno'];
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => $this->alumno->nombre,
				'help_text' => 'El nombre o nombres del alumno',
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
				'initial' => $this->alumno->apellido,
				'help_text' => 'Los apellidos del alumno',
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
				'initial' => $this->alumno->sexo,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => array ('Masculino' => 'M', 'Femenino' => 'F'),
				),
		));
		
		$this->fields['correo'] = new Gatuf_Form_Field_Email (
			array (
				'required' => true,
				'label' => 'Correo',
				'initial' => $this->alumno->user->email,
				'help_text' => 'Un correo',
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the model from and invalid form.');
		}
		
		$this->alumno->setFromFormData ($this->cleaned_data);//codigo = $this->cleaned_data['codigo'];
		/*$this->alumno->nombre = $this->cleaned_data['nombre'];
		$this->alumno->apellido = $this->cleaned_data['apellido'];
		$this->alumno->sexo = $this->cleaned_data['sexo'];*/
		
		//$this->alumno->user->login = $this->cleaned_data['codigo'];
		$this->alumno->user->email = $this->cleaned_data['correo'];
		//$this->alumno->user->type = 'a';
		//$this->alumno->user->administrator = false;
		
		if ($commit) {
			$this->alumno->update();
			$this->alumno->user->update ();
		}
		return $this->alumno;
	}
}
