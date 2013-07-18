<?php

class Gatuf_Permission extends Gatuf_Model {
	public $id;
	public $name, $code_name;
	public $description;
	public $application;
	
	public function __construct () {
		$this->_getConnection ();
		$this->tabla = 'permisos';
	}
	
	public static function getFromString ($perm) {
		list($app, $code) = explode ('.', trim ($perm));
		$sql = new Gatuf_SQL ('code_name=%s AND application=%s', array ($code, $app));
		
		$perms = Gatuf::factory ('Gatuf_Permission')->getList (array ('filter' => $sql->gen ()));
		
		if ($perms->count () != 1) {
			return false;
		}
		
		return $perms[0];
	}
}
