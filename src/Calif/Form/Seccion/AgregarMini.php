<?php

class Calif_Form_Seccion_AgregarMini extends Gatuf_Form {
	private $user;
	
	public function initFields($extra=array()) {
		$this->user = $extra['user'];
		
		$materia = '';
		if (isset ($extra['materia'])) $materia = $extra['materia'];
		
		$choices = array ();
		$carreras = $this->user->returnCoord ();
		$carrera_model = new Calif_Carrera ();
		foreach ($carreras as $carrera) {
			$carrera_model->get (substr ($carrera, 18));
			
			$choices[$carrera_model->descripcion] = array ();
			
			foreach ($carrera_model->get_catalogo_list (array ('order' => 'descripcion ASC')) as $m) {
				$choices[$carrera_model->descripcion][$m->descripcion] = $m->clave;
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
	}
	
	/* Verificar que la materia pertenezca a una carrera que el usuario coordine */
	public function clean_materia () {
		$materia = $this->cleaned_data['materia'];
		
		$materia_model = new Calif_Materia ();
		$materia_model->get ($materia);
		$carreras = $materia_model->get_carreras_list ();
		
		$permiso = false;
		foreach ($carreras as $carrera) {
			if ($this->user->hasPerm ('SIIAU.coordinador.'.$carrera->clave)) $permiso = true;
		}
		
		if ($permiso === false) {
			throw new Gatuf_Form_Invalid ('No puede crear secciones para materias de carreras que usted no coordina');
		}
		
		return $materia;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$seccion = new Calif_Seccion ();
		
		/* Generar un NRC */
		$max_nrc = Gatuf::factory ('Calif_Seccion')->maxNrc () + 1;
		
		if ($max_nrc < 95000) $max_nrc = 95000;
		
		/* Verificar que este nrc no esté duplicado */
		do {
			$sql = new Gatuf_SQL('nrc=%s', array($max_nrc));
			$l = Gatuf::factory('Calif_Seccion')->getList(array('filter'=>$sql->gen(),'count' => true));
			
			if ($l > 0) $max_nrc++;
			if ($max_nrc > 99999) {
				throw new Exception ('Imposible obtener un NRC inventado. Por favor llame al administrador');
			}
		} while ($l > 0);
		
		$seccion->nrc = $max_nrc;
		$seccion->setFromFormData ($this->cleaned_data);
		
		/* Verificar que nuestro amigo staff exista */
		$maestro = new Calif_Maestro ();
		if (false === ($maestro->get ('2222222'))) {
			$maestro->codigo = 2222222;
			$maestro->nombre = '(Sin profesor)';
			$maestro->apellido = 'Sin profesor';
			$maestro->correo = '--invalido--';
			
			$maestro->create ();
			//$maestro->active = false;
		}
		
		$seccion->maestro = $maestro;
		
		/* Generar sección */
		$ultima_seccion = Gatuf::factory ('Calif_Seccion')->maxSeccion ($this->cleaned_data['materia']);
		if (is_null ($ultima_seccion)) {
			$num_sec = 'D01';
		} else {
			preg_match ('/^[dD](\d+)$/', $ultima_seccion, $match);
			
			$numero = (int) $match[1];
			$numero++;
			$num_sec = 'D'.str_pad ($numero, 2, '0', STR_PAD_LEFT);
		}
		
		$seccion->seccion = $num_sec;
		
		if ($commit) $seccion->create();
		
		return $seccion;
	}
}
