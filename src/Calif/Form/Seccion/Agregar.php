<?php

class Calif_Form_Seccion_Agregar extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['nrc'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'NRC',
				'initial' => '',
				'help_text' => 'El Nrc del grupo',
				'max_length' => 5,
				'widget_attrs' => array(
					'maxlength' => 5,
				),
		));
		
		$materia = '';
		if (isset ($extra['materia'])) $materia = $extra['materia'];
		
		$todaslasmaterias = Gatuf::factory ('Calif_Materia')->getList ();
		$choices = array ();
		foreach ($todaslasmaterias as $m) {
			$choices [$m->clave . ' - ' . $m->descripcion] = $m->clave;
		}
		
		$this->fields['materia'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Materia',
				'initial' => $materia,
				'help_text' => 'El nombre completo de la materia',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['seccion'] = new Gatuf_Form_Field_Varchar(
			array (
				'required' => true,
				'label' => 'Seccion',
				'initial' => 'D',
				'help_text' => 'La sección, como D01 o D23',
				'max_length' => 4,
				'widget_attrs' => array(
					'maxlength' => 4,
				)
		));
		
		$todoslosmaestros = Gatuf::factory ('Calif_Maestro')->getList (
		                    array ('order' => array ('Apellido ASC', 'Nombre ASC')));
		$choices = array ();
		foreach ($todoslosmaestros as $m) {
			$choices[$m->apellido . ' ' . $m->nombre] = $m->codigo;
		}
		
		$this->fields['maestro'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Maestro',
				'initial' => '',
				'help_text' => 'El maestro de este grupo',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function clean_nrc () {
		$nrc = $this->cleaned_data ['nrc'];
		
		if (!preg_match ("/^\d+$/", $nrc)) {
			throw new Gatuf_Form_Invalid ('El nrc debe ser numérico');
		}
		
		/* Verificar que este nrc no esté duplicado */
		$sql = new Gatuf_SQL('Nrc=%s', array($nrc));
        $l = Gatuf::factory('Calif_Seccion')->getList(array('filter'=>$sql->gen(),'count' => true));
        if ($l > 0) {
            throw new Gatuf_Form_Invalid('Este NRC ya existe');
        }
        
        return $nrc;
	}
	
	public function clean_seccion () {
		$seccion = mb_strtoupper($this->cleaned_data['seccion']);
		
		if (!preg_match ("/^[dD]\d+$/", $seccion)) {
			throw new Gatuf_Form_Invalid('La sección de la materia tiene que comenzar con "D" y ser númerica');
		}
		
		return $seccion;
	}
	
	/* Verificar que la materia y la sección no estén duplicados */
	public function clean () {
		$materia = $this->cleaned_data['materia'];
		$seccion = $this->cleaned_data['seccion'];
		
		$sql = new Gatuf_SQL ('Seccion=%s', $seccion);
		$sql->Q ('Materia=%s', $materia);
		$l = Gatuf::factory ('Calif_Seccion')->getList (array ('filter'=>$sql->gen(), 'count' => true));
		
		if ($l > 0) {
			throw new Gatuf_Form_Invalid ('La materia ya tiene esta misma sección');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$seccion = new Calif_Seccion ();
		
		$seccion->nrc = $this->cleaned_data['nrc'];
		$seccion->materia = $this->cleaned_data['materia'];
		$seccion->seccion = $this->cleaned_data['seccion'];
		$seccion->maestro = $this->cleaned_data['maestro'];
		
		$seccion->create();
		
		return $seccion;
	}
}