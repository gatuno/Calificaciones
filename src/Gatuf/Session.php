<?php
/* -*- tab-width: 4; indent-tabs-mode: nil; c-basic-offset: 4 -*- */
/*
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Plume Framework, a simple PHP Application Framework.
# Copyright (C) 2001-2007 Loic d'Anterroches and contributors.
#
# Plume Framework is free software; you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation; either version 2.1 of the License, or
# (at your option) any later version.
#
# Plume Framework is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
#
# ***** END LICENSE BLOCK ***** */

/*
 * Crappy version of the Pluf_Session model
 * Yes, very crappy
 */
class Gatuf_Session {
	public $tabla = 'sessions';
	public $data = array();
	public $cookie_name = 'sessionid';
	public $touched = false;
	public $test_cookie_name = 'testcookie';
	public $test_cookie_value = 'worked';
	public $set_test_cookie = false;
	public $test_cookie = null;
	
	public $_con = null;
	
	/* Campos de la tabla */
	public $session_key, $session_data, $expire;
	
	public function __construct {
	    $this->_getConnection();
		$this->session_key = '';
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
			$this->session_key = $object->session_key;
			$this->session_data = $object->session_data;
			$this->expire = $object->expire;
			$res[] = clone ($this);
		}
		
		mysql_free_result ($result);
		
		return $res;
	}
	
	function get ($session_key) {
		$req = sprintf ('SELECT * FROM %s WHERE session_key=%s', Gatuf_DB_esc ($clave));
		
		$result = mysql_query ($req, $this->_con);
		
		if (mysql_num_rows ($result) == 0) {
			return false;
		} else {
			$object = mysql_fetch_object ($result);
			$this->session_key = $object->session_key;
			$this->session_data = $object->session_data;
			$this->expire = $object->expire;
			
			mysql_free_result ($result);
		}
		self::restore ();
		return true;
	}
	
	function create () {
		$this->preSave();
        
		$req = sprintf ('INSERT INTO %s (session_key, session_data, expire) VALUES (%s, %s, %s)', $this->tabla, Gatuf_DB_esc ($this->session_key), Gatuf_DB_esc ($this->session_data), Gatuf_DB_esc ($this->expire));
		
		$res = mysql_query ($req);
		
		if ($res === false) {
			throw new Exception ('Error en la query: '.$req.', el error devuelto por mysql es: '.mysql_errno ($this->_con).' - '.mysql_error ($this->_con));
		}
		return true;
	}
	
	function update () {
		$this->preSave();
		$req = sprintf ('UPDATE %s SET session_data=%s, expire=%s WHERE session_keys=%s', $this->tabla, Gatuf_DB_esc ($this->session_data), Gatuf_DB_esc ($this->expire), Gatuf_DB_esc ($this->session_key));
		
		$res = mysql_query ($req);
		
		if ($res === false) {
			throw new Exception ('Error en la query: '.$req.', el error devuelto por mysql es: '.mysql_errno ($this->_con).' - '.mysql_error ($this->_con));
		}
		return true;
	}
	
	function setData($key, $value=null) {
		if (is_null($value)) {
			unset($this->data[$key]);
		} else {
			$this->data[$key] = $value;
		}
		$this->touched = true;
	}
	
	function getData($key, $default='') {
		if (isset($this->data[$key])) {
			return $this->data[$key];
		} else {
			return $default;
		}
	}
	
	function clear() {
		$this->data = array();
		$this->touched = true;
	}
	
	/**
	 * Generate a new session key.
	 */
	function getNewSessionKey() {
		while (1) {
			$key = md5(microtime().rand(0, 123456789).rand(0, 123456789).Gatuf::config('secret_key'));
			$sess = $this->getList(array('filter' => 'session_key=\''.$key.'\''));
			if (count($sess) == 0) {
				break;
			}
		}
		return $key;
	}
	
	function preSave($create=false) {
		$this->session_data = serialize($this->data);
		if ($this->session_key == '') {
			$this->session_key = $this->getNewSessionKey();
		}
		$this->expire = gmdate('Y-m-d H:i:s', time()+31536000);
	}
	
	function restore() {
		$this->data = unserialize($this->session_data);
	}
    
	/**
	 * Create a test cookie.
	 */
	public function createTestCookie() {
		$this->set_test_cookie = true;
	}
	
	public function getTestCookie() {
		return ($this->test_cookie == $this->test_cookie_value);
	}
	
	public function deleteTestCookie() {
		$this->set_test_cookie = true;
		$this->test_cookie_value = null;
	}
}
