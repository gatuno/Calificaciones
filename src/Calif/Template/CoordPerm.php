<?php

class Calif_Template_CoordPerm extends Gatuf_Template_Tag {
	function start ($var, $user, $carrera) {
		$this->context->set($var, $user->hasPerm('SIIAU.coordinador.'.$carrera));
	}
}
