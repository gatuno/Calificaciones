<?php

class Calif_Form_Alumno_Agregar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$this->fields['codigo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Código',
				'initial' => '',
				'help_text' => 'El código de alumno de 9 caracteres',
				'max_length' => 9,
				'min_length' => 9,
				'widget_attrs' => array (
					'maxlength' => 9,
					'size' => 12,
				),
		));
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => '',
				'help_text' => 'El nombre o nombres del alumno',
				'max_length' => 50,
				'min_length' => 5,
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
				'help_text' => 'Los apellidos del alumno',
				'max_length' => 100,
				'min_length' => 5,
				'widget_attrs' => array (
					'maxlength' => 100,
					'size' => 30,
				),
		));
		/* Preparar las carreras */
		$carreras = Gatuf::factory ('Calif_Carrera')->getList (array ('order' => array ('Clave ASC')));
		
		$choices = array();
		foreach ($carreras as $car) {
			$choices [$car->clave.' - '.$car->descripcion] = $car->clave;
		}
		
		$this->fields['carrera'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Carrera',
				'initial' => '',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'help_text' => 'La carrera del estudiante',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['correo'] = new Gatuf_Form_Field_Email (
			array (
				'required' => true,
				'label' => 'Correo',
				'initial' => '',
				'help_text' => 'Un correo',
		));
	}
	
	public function clean_codigo () {
		$codigo = mb_strtoupper($this->cleaned_data['codigo']);
		
		if (!preg_match ('/^\w\d{8}$/', $codigo)) {
			throw new Gatuf_Form_Invalid ('El código del alumno es incorrecto');
		}
		
		$sql = new Gatuf_SQL ('codigo=%s', array ($codigo));
		$l = Gatuf::factory('Calif_Alumno')->getList(array ('filter' => $sql->gen(), 'count' => true));
		
		if ($l > 0) {
			throw new Gatuf_Form_Invalid (sprintf ('El código \'<a href="%s">%s</a>\' de alumno especificado ya existe', Gatuf_HTTP_URL_urlForView('Calif_Views_Alumno::verAlumno', array ($codigo)), $codigo));
		}
		
		return $codigo;
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the model from and invalid form.');
		}
		
		$alumno = new Calif_Alumno ();
		
		$alumno->codigo = $this->cleaned_data['codigo'];
		$alumno->nombre = $this->cleaned_data['nombre'];
		$alumno->apellido = $this->cleaned_data['apellido'];
		$alumno->carrera = $this->cleaned_data['carrera'];
		$alumno->correo = $this->cleaned_data['correo'];
		
		$alumno->create();
		
		return $alumno;
	}
}
