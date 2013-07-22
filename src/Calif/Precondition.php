<?php

class Calif_Precondition {
	static public function isCoord($request) {
		$res = Gatuf_Precondition::loginRequired($request);
		if (true !== $res) {
			return $res;
		}
		if ($request->user->isCoord()) {
			return true;
		}
		return new Gatuf_HTTP_Response_Forbidden($request);
	}
}
