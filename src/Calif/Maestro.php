<?php

class Calif_Maestro extends Gatuf_Model {
	/* Manejador de la tabla Maestros */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'maestros';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'codigo';
		
		$this->_a['cols'] = array (
			'codigo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 50,
			),
			'apellido' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 100,
			),
			'sexo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'default' => 'M',
			       'size' => 1,
			),
			'grado' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'default' => 'L',
			       'size' => 1,
			),
			'asignatura' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Calif_Nombramiento',
			       'default' => '1001H',
			),
			'nombramiento' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Calif_Nombramiento',
			       'default' => null,
			       'is_null' => true,
			),
			'tiempo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			       'default' => null, /* Tiempo Completo, Medio tiempo */
			       'is_null' => true,
			),
		);
		
		$this->default_order = 'apellido ASC, nombre ASC';
	}
	
	function getUser () {
		$sql = new Gatuf_SQL ('login=%s', $this->codigo);
		$this->user = Gatuf::factory ('Calif_User')->getOne (array ('filter' => $sql->gen ()));
	}
	
	function maxTiempoCompleto () {
		if ($this->nombramiento === null) {
			return 0;
		}
		
		if (substr ($this->nombramiento, 0, 2) == '30') {
			/* Los técnicos académicos no tienen carga */
			return 0;
		}
		if (substr ($this->nombramiento, 0, 2) == '20') {
			$nom = new Calif_Nombramiento ($this->nombramiento);
			if ($this->tiempo == 't') {
				return $nom->horas;
			} else {
				return ($nom->horas / 2);
			}
		}
	}
	
	function maxAsignatura () {
		if ($this->nombramiento === null) {
			return 48;
		}
		
		$nom = new Calif_Nombramiento ($this->nombramiento);
		if (substr ($this->nombramiento, 0, 2) == '30') {
			if ($this->tiempo == 't') {
				return 8;
			} else {
				return 28;
			}
		}
		if (substr ($this->nombramiento, 0, 2) == '20') {
			$nom = new Calif_Nombramiento ($this->nombramiento);
			if ($this->tiempo == 't') {
				return 8;
			} else {
				return 28;
			}
		}
	}
	
	function getCarga ($tipo = 't') {
		/* SELECT carga, SUM(horas) FROM `numero_puestos` LEFT JOIN secciones ON secciones.nrc = numero_puestos.nrc WHERE maestro = 9528369 GROUP BY carga */
		$seccion_tabla = Gatuf::factory ('Calif_Seccion')->getSqlTable ();
		
		$view = array ('join' => 'NATURAL JOIN '.$seccion_tabla, 'group' => 'carga', 'select' => 'carga, SUM(horas) AS horas');
		$numero_puesto = new Calif_NumeroPuesto ();
		$numero_puesto->_a['views']['carga'] = $view;
		
		$sql = new Gatuf_SQL ('carga=%s AND (maestro=%s AND (tipo="t" OR tipo="p")) OR (suplente=%s AND (tipo="u" OR tipo="q"))', array ($tipo, $this->codigo, $this->codigo));
		
		$num = $numero_puesto->getList (array ('filter' => $sql->gen (), 'view' => 'carga'));
		
		if (count ($num) == 0) {
			$numero_puesto->carga = $tipo;
			$numero_puesto->horas = 0;
			return $numero_puesto;
		}
		
		return $num[0];
	}
	
	function displaylinkedcodigo ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Maestro::verMaestro', array ($this->codigo)).'">'.$this->codigo.'</a>';
	}
	
	function displaylinkeddepartamento ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('verHorarioPorDepartamentoPDF', array ($this->codigo, $this->departamento)).'">Imprimir Horario</a>';
	}
	
	function __toString () {
		return $this->apellido.' '.$this->nombre.' ('.$this->codigo.')';
	}
	
	function displaygrado ($extra = null) {
		switch ($this->grado) {
			case 'I':
				return 'Ing.';
				break;
			case 'L':
				return 'Lic.';
				break;
			case 'D':
				if ($this->sexo == 'F') return 'Dra.';
				else return 'Dr.';
				break;
			case 'M':
				if ($this->sexo == 'F') return 'Mtra.';
				else return 'Mtro.';
				break;
			default:
				throw new Exception ('No implementado');
		}
	}
}
