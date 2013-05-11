<?php

class Calif_User {
	public $login_tabla;
	public $password;
	public $codigo = 0;
	
	public $active = true, $last_login = null, $admin = false;
	
	function setPassword ($password) {
		$salt = Gatuf_Utils::getRandomString(5);
		$this->password = 'sha1:'.$salt.':'.sha1($salt.$password);
		return true;
	}
	
	function checkPassword ($password) {
		if ($this->password == '') {
			return false;
		}
		list ($algo, $salt, $hash) = explode(':', $this->password);
		if ($hash == $algo($salt.$password)) {
			return true;
		} else {
			return false;
		}
	}
	
	function checkCreditentials ($login, $password) {
		$where = sprintf ('Login = %s', Gatuf_DB_esc ($login));
		
		$users = $this->getLoginList (array ('filter' => $where));
		
		if ($users === false or count ($users) !== 1) {
			return false;
		}
		if ($users[0]->active and $users[0]->checkPassword($password)) {
			return $users[0];
		}
		return false;
	}
	
	function preSave () {
		if ($this->codigo !== '') {
			$this->last_login = gmdate('Y-m-d H:i:s');
		}
	}
	
	function isAnonymous () {
		return (0 === (int) $this->id);
	}
}
