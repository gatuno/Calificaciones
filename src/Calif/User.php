<?php

class Calif_User extends Gatuf_Model {
	public $_model = __CLASS__;
	public $session_key = '_GATUF_Gatuf_User_auth';
	
	public $_cache_perms = null;
	
	function init () {
		$this->_a['verbose'] = 'user';
		$this->_a['table'] = 'users';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'login' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'unique' => true,
			       'size' => 50,
			),
			'email' =>
			array (
			       'type' => 'Gatuf_DB_Field_Email',
			       'blank' => false,
			),
			'password' =>
			array (
			       'type' => 'Gatuf_DB_Field_Password',
			       'blank' => false,
			       'size' => 150,
			),
			'groups' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'blank' => true,
			       'model' => Gatuf::config ('gatuf_custom_group', 'Gatuf_Group'),
			       'relate_name' => 'users',
			),
			'permissions' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'blank' => true,
			       'model' => 'Gatuf_Permission',
			),
			'administrator' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'default' => false,
			       'blank' => true,
			),
			'active' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'default' => true,
			       'blank' => true,
			),
			'last_login' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => true,
			       'editable' => false,
			),
			'type' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			       'default' => 'a'
			),
		);
		$this->_a['idx'] = array (
			'login_idx' =>
			array (
				'col' => 'login',
				'type' => 'unique',
			),
		);
		$this->_a['views'] = array ();
	}
	
	function __toString () {
		/* FIXME: regresar nombre y apellido */
	}
	
	function preDelete () {
		$params = array ('user' => $this);
		
		Gatuf_Signal::send ('Gatuf_User::preDelete', 'Gatuf_User', $params);
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
		$where = 'login = '.$this->_toDb ($login, 'login');
		$users = $this->getList (array ('filter' => $where));
		
		if ($users === false or count ($users) !== 1) {
			return false;
		}
		
		if ($users[0]->active and $users[0]->checkPassword($password)) {
			return $users[0];
		}
		return false;
	}
	
	function preSave ($create = false) {
		if ($this->id > 0) {
			$this->last_login = gmdate('Y-m-d H:i:s');
		}
	}
	
	function isAnonymous () {
		return (0 === (int) $this->id);
	}
	
	function getAllPermissions ($force=false) {
		if ($force == false and !is_null($this->_cache_perms)) {
			return $this->_cache_perms;
		}
		
		$this->_cache_perms = array ();
		$perms = (array) $this->get_permissions_list ();
		$groups = $this->get_groups_list ();
		$ids = array ();
		foreach ($groups as $group) {
			$ids[] = $group->id;
		}
		
		if (count ($ids) > 0) {
			$gperm = new Gatuf_Permission ();
			$f_name = strtolower (Gatuf::config ('gatuf_custom_group', 'Gatuf_Grupo')).'_id';
			$perms = array_merge ($perms, (array) $gperm->getList (array ('filter' => $f_name.' IN ('.join(', ', $ids).')', 'view' => 'join_group')));
		}
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
		if ($this->administrator) return true;
		$perms = $this->getAllPermissions ();
		
		if (in_array ($perm, $perms)) return true;
		
		return false;
	}
	
	function hasAppPerms ($app) {
		if ($this->administrator) return true;
		
		foreach ($this->getAllPermissions() as $perm) {
			if (0 === strpos ($perm, $app.'.')) return true;
		}
		return false;
	}
	
	function setMessage ($type, $message) {
		if ($this->isAnonymous ()) {
			return false;
		}
		
		$m = new Gatuf_Message ();
		$m->message = $message;
		$m->type = $type;
		$m->user = $this;
		
		return $m->create ();
	}
	
	function getAndDeleteMessages () {
		if ($this->isAnonymous ()) {
			return false;
		}
		$messages = new ArrayObject ();
		$ms = $this->get_gatuf_message_list();
		foreach ($ms as $m) {
			$messages[] = $m;
			$m->delete ();
		}
		
		return $messages;
	}
	
	function isCoord () {
		if (!$this->active) return false;
		if ($this->administrator) return true;
		
		$perms = $this->getAllPermissions ();
		
		$coords = preg_grep ('/SIIAU.coordinador.*/', $perms);
		
		if (count ($coords) > 0) return true;
		return false;
	}
	
	function returnCoord () {
		if (!$this->active) return false;
		
		$perms = $this->getAllPermissions ();
		
		$coords = preg_grep ('/SIIAU.coordinador.*/', $perms);
		
		return $coords;
	}
}
