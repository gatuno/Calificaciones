<?php

class Calif_Form_Seccion_Agregar extends Gatuf_Form {
	private $user;
	public function initFields($extra=array()) {
		$this->user = $extra['user'];
		$this->fields['nrc'] = new Gatuf_Form_Field_Integer(
			array(
				'required' => false,
				'label' => 'NRC',
				'initial' => '',
				'help_text' => 'El Nrc del grupo',
				'max' => 99999,
				'min' => 0,
				'widget_attrs' => array(
					'maxlength' => 5,
				),
		));
		
		$this->fields['auto_nrc'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'NRC automático',
				'initial' => false,
				'help_text' => 'Si el nrc debe ser generado automáticamente',
		));
		
		$materia = '';
		if (isset ($extra['materia'])) $materia = $extra['materia'];
		
		$choices = array ();
		foreach (Gatuf::factory ('Calif_Departamento')->getList () as $dep) {
			if (!$this->user->hasPerm ('SIIAU.jefe.'.$dep->clave)) continue;
			$choices[$dep->descripcion] = array ();
			foreach ($dep->get_calif_materia_list () as $m) {
				$choices[$dep->descripcion][$m->clave . ' - ' . $m->descripcion] = $m->clave;
			}
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
			$choices[$m->apellido.' '.$m->nombre] = $m->codigo;
		}
		
		$this->fields['maestro'] = new Gatuf_Form_Field_Integer(
			array(
				'required' => true,
				'label' => 'Profesor',
				'initial' => '',
				'help_text' => 'El profesor de este grupo',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));

		$choices = array('No asignado' => 0) + $choices;
		 
		$this->fields['suplente'] = new Gatuf_Form_Field_Integer(
			array(
				'required' => false,
				'label' => 'Suplente',
				'initial' => NULL,
				'help_text' => 'El suplente para este grupo',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function clean_seccion () {
		$seccion = mb_strtoupper($this->cleaned_data['seccion']);
		
		if (!preg_match ("/^[dD]\d+$/", $seccion)) {
			throw new Gatuf_Form_Invalid('La sección de la materia tiene que comenzar con "D" y ser númerica');
		}
		
		return $seccion;
	}
	
	public function clean () {
		$auto = $this->cleaned_data ['auto_nrc'];
		
		if (!$auto) {
			$nrc = $this->cleaned_data ['nrc'];
			
			if ($nrc == '' || $nrc == 0) {
				throw new Gatuf_Form_Invalid ('Es requisito escribir un NRC válido');
			}
			
			/* Verificar que este nrc no esté duplicado */
			$sql = new Gatuf_SQL('nrc=%s', $nrc);
			$l = Gatuf::factory('Calif_Seccion')->getList(array('filter'=>$sql->gen(),'count' => true));
			if ($l > 0) {
				throw new Gatuf_Form_Invalid('El NRC especificado ya existe');
			}
		}
		
		/* Verificar que la materia y la sección no estén duplicados */
		$materia = $this->cleaned_data['materia'];
		$seccion = $this->cleaned_data['seccion'];
		
		$sql = new Gatuf_SQL ('seccion=%s AND materia=%s', array ($seccion, $materia));
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
		
		if ($this->cleaned_data['auto_nrc']) {
			/* Generar un NRC */
			$nrc = Gatuf::factory ('Calif_Seccion')->maxNrc () + 1;
		
			if ($nrc < 95000) $max_nrc = 95000;
		
			/* Verificar que este nrc no esté duplicado */
			do {
				$sql = new Gatuf_SQL('nrc=%s', array($nrc));
				$l = Gatuf::factory('Calif_Seccion')->getList(array('filter'=>$sql->gen(),'count' => true));
			
				if ($l > 0) $nrc++;
				if ($nrc > 99999) {
					throw new Exception ('Imposible obtener un NRC inventado. Por favor llame al administrador');
				}
			} while ($l > 0);
		} else {
			$nrc = $this->cleaned_data['nrc'];
		}
		
		$seccion->nrc = $nrc;
		
		$materia = new Calif_Materia ($this->cleaned_data['materia']);
		$seccion->materia = $materia;
		$seccion->seccion = $this->cleaned_data['seccion'];
		
		$maestro = new Calif_Maestro ($this->cleaned_data['maestro']);
		$seccion->maestro = $maestro;
		if ($this->cleaned_data['suplente'] != 0){
			$seccion->suplente = new Calif_Maestro ($this->cleaned_data['suplente']);
		}
		else{
			$seccion->suplente = null;
		}
		if ($commit) $seccion->create();
		
		return $seccion;
	}
}
