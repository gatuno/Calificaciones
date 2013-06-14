<?php
/* Gatuf's Creepy Version of the model */

Gatuf::loadFunction ('Gatuf_DB_getConnection');

class Gatuf_Model {
	/** Database connection. */
	public $_con = null;
    
    public $tabla, $tabla_view;
    public $default_order = '';
    
	function _getConnection () {
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
    
	function getSqlTable () {
		return $this->_con->pfx.$this->tabla;
	}
	
	function getSqlViewTable () {
		return $this->_con->pfx.($this->tabla_view == '' ? $this->tabla : $this->tabla_view);
	}
	
	function getList ($p=array()) {
		$default = array('filter' => null,
                         'order' => null,
                         'start' => null,
                         'select' => null,
                         'nb' => null,
                         'count' => false);
        $p = array_merge ($default, $p);
        $query = array(
                       'select' => '*',
                       'from' => $this->getSqlViewTable(),
                       'join' => '',
                       'where' => '',
                       'group' => '',
                       'having' => '',
                       'order' => $this->default_order,
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
				$p['order'] .= ', ';
			}
			$query['order'] = $p['order'].$query['order'];
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
		
		if (false === ($rs=$this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count($rs) == 0) {
			return array ();
		}
		
		if ($p['count'] == true) {
			if (empty($rs) or count($rs) == 0) { 
				return 0; 
			} else {
				return (int) $rs[0]['nb_items'];
			}
		}
		
		$res = array ();
		foreach ($rs as $row) {
			foreach ($row as $col => $val) {
				$this->$col = $val;
			}
			$this->restore ();
			$res[] = clone ($this);
		}
		
		return $res;
	}
	
	function getCount($p=array()) {
		$p['count'] = true;
		$count = $this->getList($p);
		return (int) $count;
	}
	
	public function displayVal ($field) {
		return $this->$field;
	}
	
	public function restore () {}
}
