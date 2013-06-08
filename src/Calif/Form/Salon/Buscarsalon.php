<?php

class Calif_Form_Salon_Buscarsalon extends Gatuf_Form {
	public function initFields($extra=array()) {
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
		
		$choices = array ('Lunes' => 'lunes',
		                  'Martes' => 'martes',
		                  'Miercoles' => 'miercoles',
		                  'Jueves' => 'jueves',
		                  'Viernes' => 'viernes',
		                  'Sabado' => 'sabado');
		$this->fields['dia'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Dia',
				'help_text' => 'El dia que quiere buscar',
				'initial' => '',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));
	}
	
	function clean () {
		/* Verificar que la hora de entrada sea siempre menor */
		if ($this->cleaned_data['horainicio'] >= $this->cleaned_data['horafin']) {
			throw new Gatuf_Form_Invalid ('Las horas de inicio y fin son inválidas');
		}
		
		return $this->cleaned_data;
	}
	
	function save ($commit=true) {
		Gatuf::loadFunction ('Calif_Utils_buscarSalonVacio');
		
		$libres = Calif_Utils_buscarSalonVacio ($this->cleaned_data['dia'], Gatuf_DB_HoraSiiauFromDb ($this->cleaned_data['horainicio']), Gatuf_DB_HoraSiiauFromDb($this->cleaned_data['horafin']));
		
		return $libres;
	}
}
