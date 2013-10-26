<?php

class Calif_Form_Seccion_AgregarMini extends Gatuf_Form {
	private $user;
	
	public function initFields($extra=array()) {
		/* Precalcular un NRC */
		$max_nrc = Gatuf::factory ('Calif_Seccion')->maxNrc ();
		
		$this->user = $extra['user'];
		
		if ($max_nrc < 80000) $max_nrc = 80000;
		$this->fields['nrc'] = new Gatuf_Form_Field_Integer(
			array(
				'required' => false,
				'label' => 'NRC',
				'initial' => $max_nrc + 1,
				'help_text' => 'Este valor será asignado por su jefe de departamento',
				'widget_attrs' => array(
					'readonly' => 'readonly'
				),
		));
		
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
		
		$this->fields['seccion'] = new Gatuf_Form_Field_Varchar(
			array (
				'required' => false,
				'label' => 'Seccion',
				'initial' => 'D--',
				'help_text' => 'Este valor será asignado por su jefe de departamento',
				'widget_attrs' => array(
					'disabled' => 'disabled',
				)
		));
		
		/*$todoslosmaestros = Gatuf::factory ('Calif_Maestro')->getList (
		                    array ('order' => array ('Apellido ASC', 'Nombre ASC')));*/
		$choices = array ();
		$choices["Staff Staff Staff"] = 1111111;
		
		$this->fields['maestro'] = new Gatuf_Form_Field_Integer (
			array(
				'required' => false,
				'label' => 'Profesor',
				'initial' => 1111111,
				'help_text' => 'Este valor será asignado por su jefe de departamento',
				'widget_attrs' => array(
					'choices' => $choices,
					'readonly' => 'readonly',
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function clean_nrc () {
		$nrc = $this->cleaned_data ['nrc'];
		
		if ($nrc == '') {
			$max_nrc = Gatuf::factory ('Calif_Seccion')->maxNrc ();
		
			if ($max_nrc < 80000) $nrc = 80000;
		}
		
		/* Verificar que este nrc no esté duplicado */
		do {
			$sql = new Gatuf_SQL('nrc=%s', array($nrc));
			$l = Gatuf::factory('Calif_Seccion')->getList(array('filter'=>$sql->gen(),'count' => true));
			
			if ($l > 0) $nrc++;
			if ($nrc > 99999) {
				throw new Exception ('Imposible obtener un NRC inventado. Por favor llame al administrador');
			}
		} while ($l > 0);
		
		return $nrc;
	}
	
	public function clean_maestro () {
		/* Verificar que nuestro amigo staff exista */
		$maestro = new Calif_Maestro ();
		if (false === ($maestro->get ('1111111'))) {
			$maestro->codigo = 1111111;
			$maestro->nombre = 'Staff';
			$maestro->apellido = 'Staff Staff';
			$maestro->correo = '--invalido--';
			
			$maestro->create ();
			//$maestro->active = false;
		}
		
		return 1111111;
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
	
	/* Generar una sección automática basada en la materia */
	public function clean () {
		$materia = $this->cleaned_data['materia'];
		
		$ultima_seccion = Gatuf::factory ('Calif_Seccion')->maxSeccion ($materia);
		if (is_null ($ultima_seccion)) {
			$seccion = 'D01';
		} else {
			preg_match ('/^[dD](\d+)$/', $ultima_seccion, $match);
			
			$numero = (int) $match[1];
			$numero++;
			$seccion = 'D'.str_pad ($numero, 2, '0', STR_PAD_LEFT);
		}
		
		$this->cleaned_data['seccion'] = $seccion;
		
		return $this->cleaned_data;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$seccion = new Calif_Seccion ();
		
		$seccion->nrc = $this->cleaned_data['nrc'];
		$materia = new Calif_Materia ($this->cleaned_data['materia']);
		$seccion->materia = $materia;
		$seccion->seccion = $this->cleaned_data['seccion'];
		$maestro = new Calif_Maestro ($this->cleaned_data['maestro']);
		$seccion->maestro = $maestro;
		
		if ($commit) $seccion->create();
		
		return $seccion;
	}
}
