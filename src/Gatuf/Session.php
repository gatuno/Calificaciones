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
class Gatuf_Session extends Gatuf_Model {
	public $data = array();
	public $cookie_name = 'sessionid'; /*FIXME: Concatenar el año para evitar conflictos */
	public $touched = false;
	public $test_cookie_name = 'testcookie';
	public $test_cookie_value = 'worked';
	public $set_test_cookie = false;
	public $test_cookie = null;
	
	public $_con = null;
	
	/* Campos de la tabla */
	public $session_key = '', $session_data, $expire;
	
	public function __construct () {
	    $this->_getConnection();
	    $this->tabla = 'sessions';
		$this->session_key = '';
		
		$this->default_query = array(
                       'select' => '*',
                       'from' => $this->getSqlTable(),
                       'join' => '',
                       'where' => '',
                       'group' => '',
                       'having' => '',
                       'order' => '',
                       'limit' => '',
                       );
	}
	
	function get ($session_key) {
		$req = sprintf ('SELECT * FROM %s WHERE session_key=%s', Gatuf_DB_IdentityToDb ($session_key, $this->_con));
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		foreach ($rs[0] as $col => $val) {
			$this->$col = $val;
		}
		
		self::restore ();
		return true;
	}
	
	function create () {
		$this->preSave();
        
		$req = sprintf ('INSERT INTO %s (session_key, session_data, expire) VALUES (%s, %s, %s)', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->session_key, $this->_con), Gatuf_DB_IdentityToDb ($this->session_data, $this->_con), Gatuf_DB_IdentityToDb ($this->expire, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function update () {
		$this->preSave();
		$req = sprintf ('UPDATE %s SET session_data=%s, expire=%s WHERE session_key=%s', $this->getSqlTable(), Gatuf_DB_IdentityToDb ($this->session_data, $this->_con), Gatuf_DB_IdentityToDb ($this->expire, $this->_con), Gatuf_DB_IdentityToDb ($this->session_key, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function delete () {
	    $this->preSave ();
	    
	    $req = sprintf ('DELETE FROM %s WHERE session_key=%s', $this->getSqlTable (), Gatuf_DB_IdentityToDb ($this->session_key, $this->_con));
	    
	    $this->_con->execute ($req);
	    $this->session_key = '';
	    $this->touched = true;
	    
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
		$this->expire = gmdate('Y-m-d H:i:s', time()+86400);
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
	
	function __get ($prop) {
	    if (isset ($this->data[$prop])) return $this->data[$prop];
	    
	    return $this->$prop ();
	}
}
