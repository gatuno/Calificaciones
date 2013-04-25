<?php

Gatuf::loadFunction ('Gatuf_DB_getConnection');

class Calif_Evaluacion {
	/* Manejador de la tabla de evaluaciones */
	
	/* Campos */
	public $id;
	public $grupo;
	public $descripcion;
	public $grupodescripcion;
	
	/* La tabla de donde recoger los datos */
	public $tabla;
	
	public $tabla_grupos;
	
	/* La conexión mysql con la base de datos */
	public $_con = null;
	
	function __construct () {
		$this->_getConnection();
		$prefix = Gatuf::config ('db_table_prefix', '');
		
		$this->tabla = $prefix.'Evaluaciones';
		$this->tabla_grupos = $prefix.'Grupos_Evaluaciones';
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
    
	function getEval ($id) {
		/* Recuperar una evaluacion */
		$sql = sprintf ('SELECT * FROM %s WHERE Id = %s', $this->tabla, Gatuf_DB_esc ($clave));
		
		$result = mysql_query ($sql, $this->_con);
		
		if (mysql_num_rows ($result) == 0) {
			return false;
		} else {
			$object = mysql_fetch_object ($result);
			$this->id = $object->Id;
			$this->grupo = $object->Grupo;
			$this->descripcion = $object->Descripcion;
			
			mysql_free_result ($result);
		}
		return true;
	}
	
	function getGruposEvals () {
		$sql = sprintf ('SELECT * FROM %s', $this->tabla_grupos);
		
		$result = mysql_query ($sql, $this->_con);
		
		$grupos_evals = array ();
		while (($object = mysql_fetch_object ($result))) {
			$grupos_evals[$object->Id] = $object->Descripcion;
		}
		
		return $grupos_evals;
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
                       'select' => 'E.Id, E.Grupo, E.Descripcion, GE.Descripcion AS GrupoDescripcion',
                       'from' => $this->tabla . ' AS E',
                       'join' => 'INNER JOIN '.$this->tabla_grupos.' AS GE ON E.Grupo = GE.Id',
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
			$this->id = $object->Id;
			$this->grupo = $object->Grupo;
			$this->descripcion = $object->Descripcion;
			$this->grupodescripcion = $object->GrupoDescripcion;
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
		$req = sprintf ('INSERT INTO %s (Grupo, Descripcion) VALUES (%s, %s);', $this->tabla, Gatuf_DB_esc ($this->grupo), Gatuf_DB_esc ($this->descripcion));
		$res = mysql_query ($req);
		
		if ($res === false) {
			throw new Exception ('Error en la query: '.$req.', el error devuelto por mysql es: '.mysql_errno ($this->_con).' - '.mysql_error ($this->_con));
		}
		$this->id = mysql_insert_id ();
		
		return true;
	}
	
	function update () {
		$req = sprintf ('UPDATE %s SET Descripcion = %s WHERE Id = %s', $this->tabla, Gatuf_DB_esc ($this->descripcion), Gatuf_DB_esc ($this->id));
		
		$res = mysql_query ($req);
		
		if ($res === false) {
			throw new Exception ('Error en la query: '.$req.', el error devuelto por mysql es: '.mysql_errno ($this->_con).' - '.mysql_error ($this->_con));
		}
		return true;
	}
	
	public function displayVal ($field) {
		return $this->$field;
	}
}
