<?php

class Gatuf_DB {
	static function get ($user, $password, $server, $dbname) {
		$con = mysql_connect ($server, $user, $password);
		if (!$con) {
			throw new Exception (mysql_errno().' - '.mysql_error());
		}
		$db = mysql_select_db ($dbname);
		if (!$db) {
			throw new Exception(mysql_errno($con).' - '.mysql_error($con));
		}
		mysql_query ('SET NAMES \'utf8\'', $con);
		
		return $con;
	}
}

function Gatuf_DB_getConnection() {
    if (isset($GLOBALS['_GATUF_db']) && 
        (is_resource($GLOBALS['_GATUF_db']) or is_object($GLOBALS['_GATUF_db']))) {
        return $GLOBALS['_GATUF_db'];
    }
    $GLOBALS['_GATUF_db'] = Gatuf_DB::get(Gatuf::config('db_login'),
                                      Gatuf::config('db_password'),
                                      Gatuf::config('db_server'),    
                                      Gatuf::config('db_database')
                                      );
    return $GLOBALS['_GATUF_db'];
}
