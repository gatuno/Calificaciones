<?php

class Calif_User {
	public $login_tabla;
	public $password;
	public $codigo = 0;
	
	public $session_key = '_GATUF_Gatuf_User_auth';
	
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
	
	function updateSession () {
		$req = sprintf ('UPDATE %s SET Last_Login = %s, Password = %s, Active = %s, Admin = %s WHERE Login = %s', $this->login_tabla, Gatuf_DB_esc ($this->last_login), Gatuf_DB_esc ($this->password), Gatuf_DB_esc ($this->active), Gatuf_DB_esc ($this->admin), Gatuf_DB_esc ($this->codigo));
		$res = mysql_query ($req);
		
		if ($res === false) {
			throw new Exception ('Error en la query: '.$req.', el error devuelto por mysql es: '.mysql_errno ($this->_con).' - '.mysql_error ($this->_con));
		}
		return true;
	}
	
	function getUser ($codigo) {
		/* Recuperar el alumno o maestro */
		if (strlen ($codigo) == 7) { 
			/* Probemos si es maestro */
			$user_model = new Calif_Maestro ();
			if ($user_model->getMaestro ($codigo) === false) return false;
		} else {
			/* En caso contrario, creemos es Alumno */
			$user_model = new Calif_Alumno ();
			if ($user_model->getAlumno ($codigo) === false) return false;
		}
		return $user_model;
	}
	
	function isAnonymous () {
		return (0 === (int) $this->codigo);
	}
}
