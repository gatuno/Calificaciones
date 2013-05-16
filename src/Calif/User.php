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
	
	function updateSession () {
		$req = sprintf ('UPDATE %s SET Last_Login = %s, Password = %s, Active = %s, Admin = %s WHERE Login = %s', $this->login_tabla, Gatuf_DB_esc ($this->last_login), Gatuf_DB_esc ($this->password), Gatuf_DB_esc ($this->active), Gatuf_DB_esc ($this->admin));
		$res = mysql_query ($req);
		
		if ($res === false) {
			throw new Exception ('Error en la query: '.$req.', el error devuelto por mysql es: '.mysql_errno ($this->_con).' - '.mysql_error ($this->_con));
		}
		return true;
	}
	
	function getUser ($codigo) {
		/* Recuperar el alumno o maestro */
        if (strlen ($user_id) == 7) { 
            /* Probemos si es maestro */
            $user_model = new Calif_Maestro ();
            $user_model->getMaestro ($user_id);
        } else {
            /* En caso contrario, creemos es Alumno */
            $user_model = new Calif_Alumno ();
            $user_model->getAlumno ($user_id);
        }
        return $user_model;
	}
	
	function isAnonymous () {
		return (0 === (int) $this->codigo);
	}
}
