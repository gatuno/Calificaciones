<?php

class Gatuf_GSetting extends Gatuf_Model {
	public $id, $vkey, $value;
	public $datacache;
	public $application;
	protected $_application;
	
	function __construct ($application = null) {
		$this->_getConnection ();
		$this->tabla = 'gsettings';
		
		if (!is_null ($application)) $this->setProject ($application);
	}
	
	function setProject ($application) {
		$this->datacache = null;
		$this->_application = $application;
	}
	
	function initCache () {
		$this->datacache = new ArrayObject ();
		$sql = new Gatuf_SQL ('application=%s', $this->_application);
		foreach ($this->getList (array ('filter' => $sql->gen ())) as $val) {
			$this->datacache[$val->vkey] = $val->value;
		}
	}
	
	function setVal ($key, $value) {
		if (!is_null ($this->getVal ($key, null))
		    and $value == $this->getVal ($key)) {
			return;
		}
		$this->delVal ($key, false);
		$conf = new Gatuf_GSetting ();
		$conf->application = $this->_application;
		$conf->vkey = $key;
		$conf->value = $value;
		$conf->create ();
		$this->initCache ();
	}
	
	function getVal ($key, $default = '') {
		if ($this->datacache === null) {
			$this->initCache ();
		}
		return (isset ($this->datacache[$key])) ? $this->datacache[$key] : $default;
	}
	
	function delVal ($key, $initcache = true) {
		$gconf = new Gatuf_GSetting ();
		$sql = new Gatuf_SQL ('vkey=%s AND application=%s', array ($key, $this->_application));
		foreach ($gconf->getList (array ('filter' => $sql->gen ())) as $c) {
			$c->delete ();
		}
		if ($initcache) {
			$this->initCache ();
		}
	}
	
	function delete () {
		$req = sprintf ('DELETE FROM %s WHERE id=%s', $this->getSqlTable (), $this->id);
		$this->_con->execute ($req);
		return true;
	}
	
	function create () {
		$req = sprintf ('INSERT INTO %s (application, vkey, value) VALUES (%s, %s, %s)', $this->getSqlTable (), Gatuf_DB_IdentityToDb ($this->application, $this->_con), Gatuf_DB_IdentityToDb ($this->vkey, $this->_con), Gatuf_DB_IdentityToDb ($this->value, $this->_con));
		$this->_con->execute ($req);
		
		$this->id = $this->_con->getLastId ();
		
		return true;
	}
}
