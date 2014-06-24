<?php

class Calif_Form_Seccion_Filtrar extends Gatuf_Form {
	private $logged;
	
	public function initFields ($extra = array ()) {
		$this->logged = $extra['logged'];
		
		$choices = array ();
		if ($this->logged) {
			$choices['Por asignación:']['Solicitadas por alguna carrera'] = 'a_X';
			$choices['Por asignación:']['No asignadas a alguna carrera'] = 'n_X';
			
			$divisiones = Gatuf::factory('Calif_Division')->getList();
			foreach ($divisiones as $division) {
				$choices['Secciones asignadas por división:'][$division->descripcion] = 'i_'.$division->id;
			}
		
			$carreras = Gatuf::factory('Calif_Carrera')->getList();
			foreach ($carreras as $carrera) {
				$choices['Secciones asignadas a una carrera:'][$carrera->descripcion] = 'c_'.$carrera->clave;
			}
			
			$choices['Por profesor:']['Con suplente asignado'] = 's_X';
		}
		
		$departamentos = Gatuf::factory('Calif_Departamento')->getList();
		foreach ($departamentos as $departamento) {
			$choices['Por departamento:'][$departamento->descripcion] = 'd_'.$departamento->clave;
		}
		
		$this->fields['filtro'] = new Gatuf_Form_Field_Varchar (
			array(
				'label' => 'Aplicar filtro',
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
		$filtro = $this->cleaned_data['filtro'];
		return $filtro;
	}
}
