<?php

class Calif_Form_Maestro_Agregar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
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
		
		$this->fields['codigo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Código',
				'initial' => '',
				'help_text' => 'El código del profesor de 7 caracteres',
				'min' => 1000000,
				'max' => 9999999,
				'widget_attrs' => array (
					'maxlength' => 7,
					'size' => 12,
				),
		));
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => '',
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
				'initial' => '',
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
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_sex
				),
		));
		
		$this->fields['grado'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Grado de estudios',
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_grados
				),
		));
		
		$this->fields['correo'] = new Gatuf_Form_Field_Email (
			array (
				'required' => true,
				'label' => 'Correo',
				'initial' => '',
				'help_text' => 'Un correo',
		));
		
		$this->fields['asignatura'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Categoría Asignatura',
				'initial' => '1001H',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_asignaturas,
				),
		));
		
		$this->fields['nombramiento'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombramiento',
				'initial' => '1000H',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_nombramientos,
				),
		));
		
		$this->fields['tiempo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Tiempo completo',
				'initial' => 'a',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_tiempo,
				),
		));
	}
	
	public function clean_codigo () {
		$codigo = $this->cleaned_data['codigo'];
		$sql = new Gatuf_SQL ('codigo=%s', array ($codigo));
		$l = Gatuf::factory('Calif_Maestro')->getList(array ('filter' => $sql->gen(), 'count' => true));
		
		if ($l > 0) {
			throw new Gatuf_Form_Invalid (sprintf ('El código "%s" del profesor especificado ya existe', $codigo));
		}
		
		return $codigo;
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
		
		$maestro = new Calif_Maestro ();
		$user = new Calif_User ();
		
		$maestro->setFromFormData ($this->cleaned_data);
		
		$user->login = $this->cleaned_data['codigo'];
		$user->email = $this->cleaned_data['correo'];
		$user->type = 'm';
		$user->administrator = false;
		
		$maestro->user = $user;
		
		if ($commit) {
			$maestro->create();
			$user->create ();
		}
		
		return $maestro;
	}
}
