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
	
	/* Campos de la tabla */
	public $session_key, $session_data, $expire;
	
	public function __construct {
		$this->session_key = '';
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
}
