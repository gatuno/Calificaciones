<?php

class Calif_User extends Gatuf_Model {
	public $login_tabla;
	public $password;
	public $codigo = '';
	
	public $session_key = '_GATUF_Gatuf_User_auth';
	
	public $active = true, $last_login = null, $admin = false;
	
	public $_cache_perms = null;
	
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
	
	function setMessage ($type, $message) {
		if ($this->isAnonymous ()) {
			return false;
		}
		
		$m = new Gatuf_Message ();
		$m->message = $message;
		$m->type = $type;
		$m->user = $this->codigo;
		
		return $m->create ();
	}
	
	function getAndDeleteMessages () {
		if ($this->isAnonymous ()) {
			return false;
		}
		$sql = new Gatuf_SQL ('user=%s', array ($this->codigo));
		$ms = Gatuf::factory('Gatuf_Message')->getList (array ('filter' => $sql->gen ()));
		foreach ($ms as $m) {
			$m->delete ();
		}
		
		return $ms;
	}
	
	function getPermissionList ($p = array ()) {
		$default = array('view' => null,
		                 'filter' => null,
		                 'order' => null,
		                 'start' => null,
		                 'nb' => null,
		                 'count' => false);
		$p = array_merge($default, $p);
		
		$m = new Gatuf_Permission ();
		$tabla = 'usuarios_permisos';
		
		$m->views['__manytomany__'] = array ();
		$m->views['__manytomany__']['join'] = ' LEFT JOIN '.$this->_con->pfx.$tabla.' ON '.$m->getSqlTable().'.id='.$this->_con->pfx.$tabla.'.permiso';
		$sql = new Gatuf_SQL ($this->_con->pfx.$tabla.'.usuario=%s', $this->codigo);
		$m->views['__manytomany__']['where'] = $sql->gen ();
		
		$p['view'] = '__manytomany__';
		return $m->getList ($p);
	}
	
	function getAllPermissions () {
		if ($this->isAnonymous ()) return array ();
		if (!is_null($this->_cache_perms)) {
			return $this->_cache_perms;
		}
		
		$this->_cache_perms = array ();
		$perms = $this->getPermissionList ();
		
		/* TODO: Gestionar los grupos aquí */
		
		foreach ($perms as $perm) {
			if (!in_array ($perm->application.'.'.$perm->code_name, $this->_cache_perms)) {
				$this->_cache_perms[] = $perm->application.'.'.$perm->code_name;
			}
		}
		return $this->_cache_perms;
	}
	
	function hasPerm ($perm, $obj = null) {
		if ($this->isAnonymous ()) return false;
		if (!$this->active) return false;
		if ($this->admin) return true;
		$perms = $this->getAllPermissions ();
		
		if (in_array ($perm, $perms)) return true;
		
		return false;
	}
	
	function isCoord () {
		if (!$this->active) return false;
		if ($this->admin) return true;
		
		$perms = $this->getAllPermissions ();
		
		$coords = preg_grep ('/SIIAU.coordinador.*/', $perms);
		
		if (count ($coords) > 0) return true;
		return false;
	}
	
	public function __get ($name) {
		return $this->$name ();
	}
}
