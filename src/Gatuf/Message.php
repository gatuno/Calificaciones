<?php

class Gatuf_Message extends Gatuf_Model {
	public $message;
	public $user;
	public $type;
	public $id;
	
	public function __construct () {
		$this->_getConnection();
		$this->tabla = 'messages';
	}
	
	function __toString () {
		return $this->message;
	}
	
	function create () {
		$req = sprintf ('INSERT INTO %s (user, message, type) VALUES (%s, %s, %s)', $this->getSqlTable (), Gatuf_DB_IdentityToDb ($this->user, $this->_con), Gatuf_DB_IdentityToDb ($this->message, $this->_con), Gatuf_DB_IntegerToDb ($this->type, $this->_con));
		$this->id = $this->_con->getLastId ();
		$this->_con->execute($req);
		return true;
	}
	
	function delete () {
		$req = sprintf ('DELETE FROM %s WHERE id=%s', $this->getSqlTable (), Gatuf_DB_IntegerToDb ($this->id, $this->_con));
		
		$this->_con->execute($req);
		$this->id = 0;
		return true;
	}
}
