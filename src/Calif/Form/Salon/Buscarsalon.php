<?php

class Calif_Form_Salon_Buscarsalon extends Gatuf_Form {
	public $semana;
	public function initFields($extra=array()) {
		$this->semana = array ();
		$this->fields['horainicio'] = new Gatuf_Form_Field_Time(
			array (
				'required' => true,
				'label' => 'Hora inicio',
				'initial' => '',
				'help_text' => 'La hora de inicio. Puede ser del tipo 17:00 o formato Siiau 1700',
		));
		
		$this->fields['horafin'] = new Gatuf_Form_Field_Time(
			array (
				'required' => true,
				'label' => 'Hora fin',
				'initial' => '',
				'help_text' => 'La hora de final. Puede ser del tipo 17:00 o formato Siiau 1700',
		));
		
		foreach (array ('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') as $dia) {
			$this->fields[$dia] = new Gatuf_Form_Field_Boolean (
				array (
					'required' => true,
					'label' => $dia,
					'initial' => '',
					'help_text' => 'Buscar dias libre en '.$dia,
					'widget' => 'Gatuf_Form_Widget_CheckboxInput',
			));
		}
	}
	
	function clean () {
		$activo = false;
		foreach (array ('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') as $dia) {
			if ($this->cleaned_data[$dia]) {
				$activo = true;
				$this->semana[] = $dia;
			}
		}
		
		if ($activo == false) {
			throw new Gatuf_Form_Invalid ('La hora para la sección debe tener al menos un día activo');
		}
		
		/* Verificar que la hora de entrada sea siempre menor */
		if ($this->cleaned_data['horainicio'] >= $this->cleaned_data['horafin']) {
			throw new Gatuf_Form_Invalid ('Las horas de inicio y fin son inválidas');
		}
		
		return $this->cleaned_data;
	}
	
	function save ($commit=true) {
		Gatuf::loadFunction ('Calif_Utils_buscarSalonVacio');
		
		$libres = Calif_Utils_buscarSalonVacio ($this->semana, Gatuf_DB_HoraSiiauFromDb ($this->cleaned_data['horainicio']), Gatuf_DB_HoraSiiauFromDb($this->cleaned_data['horafin']));
		
		return $libres;
	}
}
