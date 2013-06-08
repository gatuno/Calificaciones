<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Form_Horario_Actualizar extends Gatuf_Form {
	public $nrc;
	public $hora;
	
	public function initFields($extra=array()) {
		$this->nrc = $extra['seccion'];
		$this->hora = $extra['horario'];
		
		Gatuf::loadFunction ('Calif_Utils_displayHoraSiiau');
		
		/* Preparar la lista de salones a elegir */
		$salones = Gatuf::factory('Calif_Salon')->getList (array ('order' => array ('edificio ASC', 'aula ASC'))); /* FIXME Eliminar esto del ordenamiento */
		
		$choices = array ();
		foreach ($salones as $salon) {
			$choices[$salon->edificio.' '.$salon->aula] = $salon->id;
		}
		
		$this->fields['salon'] = new Gatuf_Form_Field_Integer(
			array (
				'required' => true,
				'label' => 'Salon',
				'initial' => $this->hora->salon,
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'help_text' => 'El salon',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['horainicio'] = new Gatuf_Form_Field_Time(
			array (
				'required' => true,
				'label' => 'Hora inicio',
				'initial' => Calif_Utils_displayHoraSiiau ($this->hora->hora_inicio),
				'help_text' => 'La hora de inicio. Puede ser del tipo 17:00 o formato Siiau 1700',
		));
		
		$this->fields['horafin'] = new Gatuf_Form_Field_Time(
			array (
				'required' => true,
				'label' => 'Hora fin',
				'initial' => Calif_Utils_displayHoraSiiau ($this->hora->hora_fin),
				'help_text' => 'La hora de final. Puede ser del tipo 17:00 o formato Siiau 1700. Recuerde que las clases terminan en el minuto 55',
		));
		
		foreach (array ('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') as $dia) {
			$this->fields[$dia] = new Gatuf_Form_Field_Boolean (
				array (
					'required' => true,
					'label' => $dia,
					'initial' => $this->hora->$dia,
					'help_text' => 'Active la casilla para una clase en '.$dia,
					'widget' => 'Gatuf_Form_Widget_CheckboxInput',
			));
		}
	}
	
	public function clean () {
		/* Verificar que por lo menos tenga un día activo */
		$activo = false;
		foreach (array ('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') as $dia) {
			if ($this->cleaned_data[$dia]) $activo = true;
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
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->hora->hora_inicio = Gatuf_DB_HoraSiiauFromDb ($this->cleaned_data['horainicio']);
		$this->hora->hora_fin = Gatuf_DB_HoraSiiauFromDb ($this->cleaned_data['horafin']);
		$this->hora->salon = $this->cleaned_data['salon'];
        
        foreach (array ('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') as $dia) {
        	$this->hora->$dia = $this->cleaned_data[$dia];
        }
        
        if ($commit) $this->hora->update ();
        
        return $this->hora;
	}
}
