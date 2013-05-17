<?php

class Gatuf_DB {
	static function get($engine, $server, $database, $login, $password, 
	                    $prefix, $debug=false, $version='') {
		$engine = 'Pluf_DB_'.$engine;
		$con = new $engine($login, $password, $server, $database, $prefix, $debug, $version);
		return $con;
	}
}

function Gatuf_DB_getConnection() {
	if (isset($GLOBALS['_GATUF_db']) && 
		(is_resource($GLOBALS['_GATUF_db']) or is_object($GLOBALS['_GATUF_db']))) {
		return $GLOBALS['_GATUF_db'];
	}
	$GLOBALS['_GATUF_db'] = Gatuf_DB::get(Gatuf::config('db_engine'),
	                                      Gatuf::config('db_server'),
	                                      Gatuf::config('db_database'),
	                                      Gatuf::config('db_login'),
	                                      Gatuf::config('db_password'),
	                                      Gatuf::config('db_table_prefix'));
    return $GLOBALS['_GATUF_db'];
}

