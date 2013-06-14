<?php

class Calif_User extends Gatuf_Model {
	public $login_tabla;
	public $password;
	public $codigo = '';
	
	public $session_key = '_GATUF_Gatuf_User_auth';
	
	public $active = true, $last_login = null, $admin = false;
	
	function getLoginSqlTable () {
		return $this->_con->pfx.$this->login_tabla;
	}
	
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
		$where = new Gatuf_SQL ('login=%s', $login);
		
		$users = $this->getLoginList (array ('filter' => $where->gen()));
		
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
	
	function getSession () {
		$req = sprintf ('SELECT * FROM %s WHERE login=%s', $this->getLoginSqlTable(), Gatuf_DB_IdentityToDb ($this->codigo, $this->_con));
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			throw new Exception ('Alto, no hay datos de sessión');
		}
		foreach ($rs[0] as $col => $val) {
			$this->$col = $val;
		}
	}
	
	function updateSession () {
		$req = sprintf ('UPDATE %s SET last_login = %s, password = %s, active = %s, admin = %s WHERE login = %s', $this->getLoginSqlTable(), Gatuf_DB_IdentityToDb ($this->last_login, $this->_con), Gatuf_DB_PasswordToDb ($this->password, $this->_con), Gatuf_DB_BooleanToDb ($this->active, $this->_con), Gatuf_DB_BooleanToDb ($this->admin, $this->_con), Gatuf_DB_IdentityToDb ($this->codigo, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function createSession () {
		$this->setPassword ('12345'); /* FIXME: Generar aleatoria y enviar por correo */
		$req = sprintf ('INSERT INTO %s (login, password, active, last_login, admin) VALUES (%s, %s, %s, %s, %s)', $this->getLoginSqlTable(), Gatuf_DB_IdentityToDb ($this->codigo, $this->_con), Gatuf_DB_PasswordToDb ($this->password, $this->_con), Gatuf_DB_BooleanToDb ($this->active, $this->_con), Gatuf_DB_IdentityToDb ($this->last_login, $this->_con), Gatuf_DB_BooleanToDB ($this->admin, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	function getUser ($codigo) {
		/* Recuperar el alumno o maestro */
		if (strlen ($codigo) == 7) { 
			/* Probemos si es maestro */
			$user_model = new Calif_Maestro ();
			if ($user_model->getMaestro ($codigo) === false) return false;
			$user_model->getSession ();
		} else {
			/* En caso contrario, creemos es Alumno */
			$user_model = new Calif_Alumno ();
			if ($user_model->getAlumno ($codigo) === false) return false;
			$user_model->getSession ();
		}
		return $user_model;
	}
	
	function isAnonymous () {
		return (0 === (int) $this->codigo);
	}
}
