<?php

class Calif_Form_Views_sobreoferta extends Gatuf_Form {
	public function initFields($extra=array()) {
		Gatuf::loadFunction ('Calif_Utils_dontmove');
		
		$this->fields['siiau'] = new Gatuf_Form_Field_File (
			array('label' => 'Seleccionar archivo',
				'help_text' => 'La oferta de siiau del departamento a verificar',
				'move_function_params' => array(),
				'max_size' => 10485760,
				'move_function' => 'Calif_Utils_dontmove'
		));
		
		$departamentos = Gatuf::factory('Calif_Departamento')->getList (array ('order' => array ('departamento ASC')));
		
		$choices = array ();
		foreach ($departamentos as $depa) {
			$choices[$depa->departamento] = $depa->clave;
		}
		$this->fields['departamento'] = new Gatuf_Form_Field_Varchar (
			array ('required' => true,
				'label' => 'Departamento',
				'initial' => '',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'help_text' => 'El departamento a verificar',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$edificios = Gatuf::factory('Calif_Edificio')->getList ();
		$choices = array ();
		foreach ($edificios as $edificio) {
			$choices[$edificio->descripcion] = $edificio->clave;
		}
		
		$this->fields['edificios'] = new Gatuf_Form_Field_Varchar (
			array('required' => true,
		          'label' => 'Edificios',
		          'initial' => array ('DEDX', 'DEDT', 'DEDU', 'DEDR', 'DEDN', 'DEDW', 'DUCT1', 'DUCT2'),
		          'multiple' => true,
		          'widget' => 'Gatuf_Form_Widget_SelectMultipleInput_Checkbox',
		          'help_text' => 'Los edificios en los que debería estar la oferta de SIIAU',
		          'widget_attrs' => array (
			      'choices' => $choices,
			      ),
		));
	}
	
	function save ($commit=true) {
		Gatuf::loadFunction ('Calif_Utils_detectarColumnas');
		Gatuf::loadFunction ('Calif_Utils_agregar_seccion');
		
		$ruta = $this->data['siiau']['tmp_name'];
		
		if (($archivo = fopen ($ruta, "r")) === false) {
			throw new Exception ('Falló al abrir el archivo '.$ruta);
		}
		
		/* Detectar cabeceras */
		$linea = fgetcsv ($archivo, 600, ',', '"');
		
		if ($linea === false || is_null ($linea)) {
			throw new Exception ('No hay cabecera, o es una linea vacia');
		}
		
		$cabecera = Calif_Utils_detectarColumnas ($linea);
		
		/* Verificar que existan los campos necesarios */
		if (!isset ($cabecera['secc']) || !isset ($cabecera['nrc'])) {
			throw new Exception ('El archivo no contiene columna de NRC o Sección');
		}
		
		if (!isset ($cabecera['clave']) || !isset ($cabecera['materia'])) {
			throw new Exception ('El archivo no contiene columna de clave de materia');
		}
		
		if (!isset ($cabecera['profesor'])) {
			throw new Exception ('El archivo no contiene columna de profesor');
		}
		
		if (!isset ($cabecera['edif']) || !isset ($cabecera['aula'])) {
			throw new Exception ('El archivo no contiene edificios o aulas');
		}
		
		if (!isset ($cabecera['ini']) || !isset ($cabecera['fin']) ||
			    !isset ($cabecera['l']) || !isset ($cabecera['m']) ||
			    !isset ($cabecera['i']) || !isset ($cabecera['j']) ||
			    !isset ($cabecera['v']) || !isset ($cabecera['s'])) {
			throw new Exception ('El archivo no contiene información sobre los horarios (hora de inicio, hora de fin, lunes, martes, ...)');
		}
		
		$secciones_por_materia = array ();
		
		/* Primera pasada, llenar todos los nrc diferentes */
		while (($linea = fgetcsv ($archivo, 600, ",", "\"")) !== FALSE) {
			if (is_null ($linea[0])) continue; /* Linea vacia */
			$clave = $linea[$cabecera['clave']];
			$nrc = $linea[$cabecera['nrc']];
			$edificio = $linea[$cabecera['edificio']];
			
			if (!isset ($secciones_por_materia[$clave]) {
				$secciones_por_materia[$clave] = array ();
			}
			
			if (!isset ($secciones_por_materia[$clave][$nrc])) {
				$secciones_por_materia[$clave][$nrc] = true;
			}
			
			if (!in_array ($edificio, $this->cleaned_data['edificios'])) {
				$secciones_por_materia[$clave][$nrc] = false;
			}
		}
		
		$materias_siiau = array ();
		foreach ($secciones_por_materia as $clave => $secciones) {
			$materias_siiau[$clave] = 0;
			
			foreach ($secciones as $seccion) {
				if ($seccion) $materias[$clave]++;
			}
		}
		
		$totales = array ();
		
		/* Recuperar la lista de materias que nos interesan */
		$sql = new Gatuf_SQL ('departamento=%s', $this->cleaned_data['departamento']);
		$materias = Gatuf::factory ('Calif_Materia')->getList (array ('filter' => $sql->gen ()));
		
		foreach ($materias as $materia) {
			$sql = new Gatuf_SQL ('materia=%s', $materia->clave);
			$no_solicitadas = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen (), 'count' => true));
			
			$totales[$materia->clave] = $materias_siiau[$materia->clave] - $no_solicitadas;
		}
		
		return $totales;
	} /* Fin del save */
} /* Fin de la clase */
