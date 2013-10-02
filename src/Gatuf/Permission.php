<?php

class Gatuf_Permission extends Gatuf_Model {
	public $id;
	public $name, $code_name;
	public $description;
	public $application;
	
	public function __construct () {
		$this->_getConnection ();
		$this->tabla = 'permissions';
		
		$tabla = 'groups_permissions';
		
		$this->views['__groups_permissions__'] = array ();
		$this->views['__groups_permissions__']['tabla'] = $tabla;
		$this->views['__groups_permissions__']['join'] = ' LEFT JOIN '.$this->_con->pfx.$tabla.' ON '.$this->getSqlViewTable ().'.id='.$this->_con->pfx.$tabla.'.permission';
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
	
	public function getGroupsList ($p = array ()) {
		$default = array('view' => null,
		                 'filter' => null,
		                 'order' => null,
		                 'start' => null,
		                 'nb' => null,
		                 'count' => false);
		$p = array_merge ($default, $p);
		
		$g = new Gatuf_Group ();
		$sql = new Gatuf_SQL ($this->_con->pfx.$g->views['__groups_permissions__']['tabla'].'.permission=%s', $this->id);
		
		$g->views['__groups_permissions__']['where'] = $sql->gen ();
		
		$p['view'] = '__groups_permissions__';
		
		return $permi->getList ($p);
	}
}
