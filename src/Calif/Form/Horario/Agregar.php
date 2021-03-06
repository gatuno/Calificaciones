<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Form_Horario_Agregar extends Gatuf_Form {
	public $nrc;
	public function initFields($extra=array()) {
		$this->nrc = $extra['seccion'];
		
		/* Preparar la lista de salones a elegir */
		$edificios = Gatuf::factory('Calif_Edificio')->getList ();
		
		$choices = array ();
		foreach ($edificios as $edificio) {
			$sql = new Gatuf_SQL ('edificio=%s', $edificio->clave);
			$salones = Gatuf::factory('Calif_Salon')->getList (array ('filter' => $sql->gen ()));
			$choices[$edificio->descripcion] = array ();
			foreach ($salones as $salon) {
				$choices[$edificio->descripcion][$salon->aula] = $salon->id;
			}
		}
		
		$this->fields['salon'] = new Gatuf_Form_Field_Integer(
			array (
				'required' => true,
				'label' => 'Salon',
				'initial' => '',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'help_text' => 'El salon',
				'widget' => 'Gatuf_Form_Widget_DobleInput',
		));
		
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
				'help_text' => 'La hora de final. Puede ser del tipo 17:00 o formato Siiau 1700. Recuerde que las clases terminan en el minuto 55',
		));
		
		foreach (array ('l' => 'Lunes', 'm' => 'Martes', 'i' => 'Miércoles', 'j' => 'Jueves', 'v' => 'Viernes', 's' => 'Sábado') as $key => $dia) {
			$this->fields[$key] = new Gatuf_Form_Field_Boolean (
				array (
					'required' => true,
					'label' => $dia,
					'initial' => '',
					'help_text' => 'Active la casilla para una clase en '.mb_strtolower ($dia),
					'widget' => 'Gatuf_Form_Widget_CheckboxInput',
			));
		}
	}
	
	public function clean () {
		/* Verificar que por lo menos tenga un día activo */
		$activo = false;
		foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
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
        
        $horario = new Calif_Horario ();
        
        $horario->nrc = $this->nrc;
        $horario->inicio = $this->cleaned_data['horainicio'];
        $horario->fin = $this->cleaned_data['horafin'];
        $salon = new Calif_Salon ($this->cleaned_data['salon']);
        $horario->salon = $salon;
        
        foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
        	$horario->$dia = $this->cleaned_data[$dia];
        }
        
        if ($commit) $horario->create ();
        
        return $horario;
	}
}
