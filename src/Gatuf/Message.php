<?php

class Gatuf_Message extends Gatuf_Model {
	public $_model = __CLASS__;
	
	public function init () {
		$this->_a['table'] = 'messages';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'user' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'message' =>
			array (
			       'type' => 'Gatuf_DB_Field_Text',
			       'blank' => false,
			),
		);
	}
	
	function __toString () {
		return $this->message;
	}
}
