<?php

class Calif_Form_Maestro_Actualizar extends Gatuf_Form {
	public $maestro;
	
	public function initFields ($extra = array ()) {
		$this->maestro = $extra['maestro'];
		
		/* Preparar catalogos */
		$choices_grados = array ('Lic.' => 'L', 'Ing.' => 'I', 'Mtro./Mtra' => 'M', 'Dr./Dra.' => 'D');
		$choices_sex = array ('Masculino' => 'M', 'Femenino' => 'F');
		$choices_tiempo = array ('Profesor de asignatura' => 'a', 'Tiempo completo' => 't', 'Medio tiempo' => 'm');
		
		$choices_asignaturas = array ();
		$sql = new Gatuf_SQL ('clave LIKE %s', '100%');
		
		foreach (Gatuf::factory ('Calif_Nombramiento')->getList (array ('filter' => $sql->gen ())) as $nomb) {
			$choices_asignaturas[$nomb->descripcion] = $nomb->clave;
		}
		
		$choices_nombramientos = array ('Sin nombramiento' => '0000H');
		$sql = new Gatuf_SQL ('clave NOT LIKE %s', '100%');
		foreach (Gatuf::factory ('Calif_Nombramiento')->getList (array ('filter' => $sql->gen ())) as $nomb) {
			$choices_nombramientos[$nomb->descripcion] = $nomb->clave;
		}
		
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
		
		$this->fields['asignatura'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Categoría asignatura',
				'initial' => $this->maestro->asignatura,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_asignaturas,
				),
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
		
		$this->fields['tiempo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Tiempo completo',
				'initial' => ($this->maestro->tiempo === null) ? 'a' : $this->maestro->tiempo,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_tiempo,
				),
		));
	}
	
	public function clean () {
		$nombramiento = $this->cleaned_data['nombramiento'];
		$tiempo = $this->cleaned_data['tiempo'];
		
		if ($nombramiento == '0000H' && $tiempo != 'a') {
			throw new Gatuf_Form_Invalid ('Un maestro de asignatura no puede ser de tiempo completo o medio tiempo');
		}
		
		if ($nombramiento != '0000H' && $tiempo == 'a') {
			throw new Gatuf_Form_Invalid ('Un maestro con nombramiento requiere tiempo completo o medio tiempo');
		}
		
		if ($nombramiento == '0000H') {
			$this->cleaned_data['nombramiento'] = null;
			$this->cleaned_data['tiempo'] = null;
		}
		
		return $this->cleaned_data;
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
