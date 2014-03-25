<?php

class Calif_Template_JefePerm extends Gatuf_Template_Tag {
	function start ($var, $user, $departamento) {
		$this->context->set($var, $user->hasPerm('SIIAU.jefe.'.$departamento));
	}
}
