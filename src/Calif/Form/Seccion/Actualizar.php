<?php

class Calif_Form_Seccion_Actualizar extends Gatuf_Form {
	public $seccion;
	
	public function initFields($extra=array()) {
		$this->seccion = $extra['seccion'];
		
		$this->fields['nrc'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'NRC',
				'initial' => $this->seccion->nrc,
				'help_text' => 'El Nrc del grupo',
		));
		
		$this->fields['materia'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => false,
				'label' => 'Materia',
				'initial' => $this->seccion->materia,
				'help_text' => 'El nombre completo de la materia',
				'widget_attrs' => array(
					'readonly' => 'readonly',
					'disabled' => 'disabled'
				),
		));
		
		$this->fields['seccion'] = new Gatuf_Form_Field_Varchar(
			array (
				'required' => true,
				'label' => 'Seccion',
				'initial' => $this->seccion->seccion,
				'help_text' => 'La sección, como D01 o D23',
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
				'label' => 'Profesor',
				'initial' => $this->seccion->maestro,
				'help_text' => 'El profesor de este grupo',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function clean_nrc () {
		$nrc = $this->cleaned_data['nrc'];
		
		if (!preg_match ("/^\d+$/", $nrc)) {
			throw new Gatuf_Form_Invalid ('El nrc debe ser numérico');
		}
		
		if ($this->seccion->nrc == $nrc) return $this->seccion->nrc;
		
		$sql = new Gatuf_SQL ('nrc=%s', $nrc);
		
		$l = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen (), 'count' => true));
		if ($l > 0) {
			throw new Gatuf_Form_Invalid ('El nrc ya está en uso');
		}
		
		return $nrc;
	}
	
	public function clean_seccion () {
		$seccion = mb_strtoupper($this->cleaned_data['seccion']);
		
		if ($this->seccion->seccion == $seccion) return $this->seccion->seccion;
		
		if (!preg_match ("/^[dD]\d+$/", $seccion)) {
			throw new Gatuf_Form_Invalid('La sección de la materia tiene que comenzar con "D" y ser númerica');
		}
		
		$sql = new Gatuf_SQL ('materia=%s AND seccion=%s', array ($this->seccion->materia, $seccion));
		
		$l = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen (), 'count' => true));
		if ($l > 0) {
			throw new Gatuf_Form_Invalida ('La sección '.$seccion.' para la materia '.$this->seccion->materia.' ya está en uso');
		}
		
		return $seccion;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$maestro = new Calif_Maestro ($this->cleaned_data['maestro']);
		if ($maestro->codigo != $this->seccion->maestro) {
			$this->seccion->maestro = $maestro;
			$params = array ('nrc' => $this->seccion);
			Gatuf_Signal::send ('Calif_Seccion::maestroUpdated', 'Calif_Form_Seccion_Actualizar', $params);
		}
		
		$this->seccion->seccion = $this->cleaned_data['seccion'];
		$this->seccion->update();
		
		if ((int) $this->seccion->nrc != (int) $this->cleaned_data['nrc']) {
			$this->seccion->cambiaNrc ($this->cleaned_data['nrc']);
		}
		return $this->seccion;
	}
}
