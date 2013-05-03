<?php

Gatuf::loadFunction ('Gatuf_DB_getConnection');

class Calif_Materia {
	/* Manejador de la tabla de carreras */
	
	/* Campos */
	public $clave;
	public $descripcion;
	
	public $misevals;
	
	/* La tabla de donde recoger los datos */
	public $tabla;
	public $tabla_porcentajes;
	
	/* La conexión mysql con la base de datos */
	public $_con = null;
	
	function __construct () {
		$this->_getConnection();
		$prefix = Gatuf::config ('db_table_prefix', '');
		
		$this->tabla = $prefix.'Materias';
		$this->tabla_porcentajes = $prefix.'Porcentajes';
		$this->misevals = array ();
	}
	
	function _getConnection() {
		static $con = null;
		if ($this->_con !== null) {
			return $this->_con;
		}
		if ($con !== null) {
			$this->_con = $con;
			return $this->_con;
		}
		$this->_con = &Gatuf::db($this);
		$con = $this->_con;
		return $this->_con;
    }
    
	function getMateria ($clave) {
		/* Recuperar una carrera */
		$sql = sprintf ('SELECT * FROM %s WHERE Clave = %s', $this->tabla, Gatuf_DB_esc ($clave));
		
		$result = mysql_query ($sql, $this->_con);
		
		if (mysql_num_rows ($result) == 0) {
			return false;
		} else {
			$object = mysql_fetch_object ($result);
			$this->clave = $object->Clave;
			$this->descripcion = $object->Descripcion;
			
			mysql_free_result ($result);
		}
		return true;
	}
	
	function getList ($p=array()) {
		$default = array('filter' => null,
                         'order' => null,
                         'start' => null,
                         'select' => null,
                         'nb' => null,
                         'count' => false);
        $p = array_merge($default, $p);
        $query = array(
                       'select' => '*',
                       'from' => $this->tabla,
                       'join' => '',
                       'where' => '',
                       'group' => '',
                       'having' => '',
                       'order' => '',
                       'limit' => '',
                       );
        
		if (!is_null($p['select'])) {
			$query['select'] = $p['select'];
		}
		/* Activar los filtros where */
        if (!is_null($p['filter'])) {
			if (is_array($p['filter'])) {
				$p['filter'] = implode(' AND ', $p['filter']);
			}
			if (strlen($query['where']) > 0) {
				$query['where'] .= ' AND ';
			}
			$query['where'] .= ' ('.$p['filter'].') ';
        }
        
        /* Elegir el orden */
		if (!is_null($p['order'])) {
			if (is_array($p['order'])) {
				$p['order'] = implode(', ', $p['order']);
			}
			if (strlen($query['order']) > 0 and strlen($p['order']) > 0) {
				$query['order'] .= ', ';
			}
			$query['order'] .= $p['order'];
		}
		/* El número de objetos a elegir */
		if (!is_null($p['start']) && is_null($p['nb'])) {
			$p['nb'] = 10000000;
		}
		/* El inicio */
		if (!is_null($p['start'])) {
			if ($p['start'] != 0) {
				$p['start'] = (int) $p['start'];
			}
			$p['nb'] = (int) $p['nb'];
			$query['limit'] = 'LIMIT '.$p['nb'].' OFFSET '.$p['start'];
		}
		if (!is_null($p['nb']) && is_null($p['start'])) {
			$p['nb'] = (int) $p['nb'];
			$query['limit'] = 'LIMIT '.$p['nb'];
		}
		/* Si la query es de conteo, cambiar el select */
		if ($p['count'] == true) {
			if (isset($query['select_count'])) {
				$query['select'] = $query['select_count'];
			} else {
				$query['select'] = 'COUNT(*) as nb_items';
			}
			$query['order'] = '';
			$query['limit'] = '';
		}
		
		/* Construir la query */
		$req = 'SELECT '.$query['select'].' FROM '.$query['from'].' '.$query['join'];
		if (strlen($query['where'])) {
			$req .= "\n".'WHERE '.$query['where'];
		}
		if (strlen($query['group'])) {
			$req .= "\n".'GROUP BY '.$query['group'];
		}
		if (strlen($query['having'])) {
			$req .= "\n".'HAVING '.$query['having'];
		}
		if (strlen($query['order'])) {
			$req .= "\n".'ORDER BY '.$query['order'];
		}
		if (strlen($query['limit'])) {
			$req .= "\n".$query['limit'];
		}
		
		$result = mysql_query ($req, $this->_con);
		
		if ($result === false) {
			throw new Exception ('Error en la query: '.$req.', el error devuelto por mysql es: '.mysql_errno ($this->_con).' - '.mysql_error ($this->_con));
		}
		if (mysql_num_rows ($result) == 0) {
			return array ();
		}
		
		if ($p['count'] == true) {
			$number = mysql_fetch_object ($result);
			mysql_free_result ($result);
			return $number->nb_items;
		}
		
		$res = array ();
		while (($object = mysql_fetch_object ($result))) {
			$this->clave = $object->Clave;
			$this->descripcion = $object->Descripcion;
			$res[] = clone ($this);
		}
		
		mysql_free_result ($result);
		
		return $res;
	}
	
	function getCount($p=array()) {
		$p['count'] = true;
		$count = $this->getList($p);
		return (int) $count;
	}
    
	function create () {
		$req = sprintf ('INSERT INTO %s (Clave, Descripcion) VALUES (%s, %s);', $this->tabla, Gatuf_DB_esc ($this->clave), Gatuf_DB_esc ($this->descripcion));
		$res = mysql_query ($req);
		
		if ($res === false) {
			throw new Exception ('Error en la query: '.$req.', el error devuelto por mysql es: '.mysql_errno ($this->_con).' - '.mysql_error ($this->_con));
		}
		return true;
	}
	
	function update () {
		$req = sprintf ('UPDATE %s SET Descripcion = %s WHERE Clave = %s', $this->tabla, Gatuf_DB_esc ($this->descripcion), Gatuf_DB_esc ($this->clave));
		
		$res = mysql_query ($req);
		
		if ($res === false) {
			throw new Exception ('Error en la query: '.$req.', el error devuelto por mysql es: '.mysql_errno ($this->_con).' - '.mysql_error ($this->_con));
		}
		return true;
	}
	
	public function getEvals ($grupo = null) {
		$eval = new Calif_Evaluacion ();
		
		$sql_filter = new Gatuf_SQL ('Materia=%s', $this->clave);
		if (!is_null ($grupo)) {
			$sql_filter->SAnd ($grupo);
		}
		
		$req = sprintf ('SELECT P.Evaluacion, P.Porcentaje, P.Abierto, P.Apertura, P.Cierre, E.Descripcion FROM %s AS P INNER JOIN %s AS E ON P.Evaluacion = E.Id WHERE %s', $this->tabla_porcentajes, $eval->tabla, $sql_filter->gen());
		
		$result = mysql_query ($req, $this->_con);
		
		if (mysql_num_rows ($result) == 0) {
			return array ();
		}
		
		$res = array ();
		while (($object = mysql_fetch_object ($result))) {
			$res[$object->Evaluacion] = array ('Porcentaje' => $object->Porcentaje,
			                                   'Abierto' => ($object->Abierto ? true : false),
			                                   'Apertura' => $object->Apertura,
			                                   'Cierre' => $object->Cierre,
			                                   'Descripcion' => $object->Descripcion
			                                   );
		}
		
		mysql_free_result ($result);
		
		return $res;
	}
	
	public function getNotEvals ($grupo = null, $count = false) {
		/* Super SQL:
		 SELECT * FROM Evaluaciones AS E WHERE NOT EXISTS (SELECT * FROM Porcentajes AS P WHERE P.Materia = 'CC100' AND E.Id = P.Evaluacion) AND Grupo = 2 */
		$eval = new Calif_Evaluacion ();
		$filtro = new Gatuf_SQL ('NOT EXISTS (SELECT * FROM '.$this->tabla_porcentajes.' WHERE '.$this->tabla_porcentajes.'.Materia=%s AND '.$this->tabla_porcentajes.'.Evaluacion='.$eval->tabla.'.Id)', $this->clave);
		
		if (!is_null ($grupo)) {
			$filtro->SAnd ($grupo);
		}
		
		$req = sprintf ('SELECT * FROM %s WHERE %s', $eval->tabla, $filtro->gen());
		
		$result = mysql_query ($req, $this->_con);
		
		if (mysql_num_rows ($result) == 0) {
			if ($count == true) return false;
			return array ();
		}
		
		if ($count == true) return true;
		$res = array ();
		while (($object = mysql_fetch_object ($result))) {
			$eval->id = $object->Id;
			$eval->descripcion = $object->Descripcion;
			$eval->grupo = $object->Grupo;
			$res[] = clone ($eval);
		}
		
		mysql_free_result ($result);
		
		return $res;
	}
	
	public function addEval ($eval, $porcentaje) {
		/* Agregar una forma de evaluación a esta materia */
		$req = sprintf ('INSERT INTO %s (Materia, Evaluacion, Porcentaje) VALUES (%s, %s, %s)', $this->tabla_porcentajes, Gatuf_DB_esc ($this->clave), Gatuf_DB_esc ($eval), Gatuf_DB_esc ($porcentaje));
		$res = mysql_query ($req);
		
		if ($res === false) {
			throw new Exception ('Error en la query: '.$req.', el error devuelto por mysql es: '.mysql_errno ($this->_con).' - '.mysql_error ($this->_con));
		}
		return true;
	}
	
	public function displayVal ($field) {
		return $this->$field;
	}
	
	public function displaylinkedclave ($extra) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Calif_Views_Materia::verMateria', array ($this->clave)).'">'.$this->clave.'</a>';
	}
}