<?php

class Calif_Form_Salon_Buscarsalon extends Gatuf_Form {
	public $semana;
	public function initFields($extra=array()) {
		$this->semana = array ();
		$this->fields['hora_inicio'] = new Gatuf_Form_Field_Time(
			array (
				'required' => true,
				'label' => 'Hora inicio',
				'initial' => '',
				'help_text' => 'La hora de inicio. Puede ser del tipo 17:00 o formato Siiau 1700',
		));
		
		$this->fields['hora_fin'] = new Gatuf_Form_Field_Time(
			array (
				'required' => true,
				'label' => 'Hora fin',
				'initial' => '',
				'help_text' => 'La hora de final. Puede ser del tipo 17:00 o formato Siiau 1700',
		));
		
		foreach (array ('l' => 'Lunes', 'm' => 'Martes', 'i' => 'Miércoles', 'j' => 'Jueves', 'v' => 'Viernes', 's' => 'Sábado') as $key => $dia) {
			$this->fields[$key] = new Gatuf_Form_Field_Boolean (
				array (
					'required' => true,
					'label' => $dia,
					'initial' => '',
					'help_text' => 'Buscar dias libre en '.mb_strtolower ($dia),
					'widget' => 'Gatuf_Form_Widget_CheckboxInput',
			));
		}
		
		$edificios = Gatuf::factory ('Calif_Edificio')->getList ();
		
		$choices = array ();
		foreach ($edificios as $edificio) {
			$choices [$edificio->descripcion] = $edificio->clave;
		}
		
		$this->fields['edificios'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Edificios',
				'initial' => $extra['edificios'],
				'help_text' => 'Puede limitar la busqueda a estos edificios',
				'widget' => 'Gatuf_Form_Widget_SelectMultipleInput_Checkbox',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'multiple' => true,
		));
	}
	
	function clean () {
		$activo = false;
		foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
			if ($this->cleaned_data[$dia]) {
				$activo = true;
				$this->semana[] = $dia;
			}
		}
		
		if ($activo == false) {
			throw new Gatuf_Form_Invalid ('Se debe buscar mínimo 1 día de la semana');
		}
		
		/* Verificar que la hora de entrada sea siempre menor */
		if ($this->cleaned_data['hora_inicio'] >= $this->cleaned_data['hora_fin']) {
			throw new Gatuf_Form_Invalid ('Las horas de inicio y fin son inválidas');
		}
		
		return $this->cleaned_data;
	}
	
	function save ($commit=true) {
		$data = array ('semana' => $this->semana, 'hora_inicio' => $this->cleaned_data['hora_inicio'], 'hora_fin' => $this->cleaned_data['hora_fin'], 'edificios' => $this->cleaned_data['edificios']);
		
		return $data;
	}
}
