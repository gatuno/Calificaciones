<?php

function Gatuf_DB_get ($user, $password, $server, $dbname) {
	$con_id = mysql_connect($server, $user, $password);
	if (!$con_id) {
        throw new Exception(Gatuf_DB_getError());
    }
    $db = mysql_select_db ($dbname);
    if (!$db) {
    	throw new Exception(Gatuf_DB_getError());
    }
    mysql_query ('SET NAMES \'utf8\'', $con_id);
    
    return $con_id;
}

function Gatuf_DB_getError () {
	return mysql_errno().' - '.mysql_error();
}

function Gatuf_DB_getConnection() {
    if (isset($GLOBALS['_GATUF_db']) && 
        (is_resource($GLOBALS['_GATUF_db']) or is_object($GLOBALS['_GATUF_db']))) {
        return $GLOBALS['_GATUF_db'];
    }
    $GLOBALS['_GATUF_db'] = Gatuf_DB_get(Gatuf::config('db_login'), 
                                      Gatuf::config('db_password'),
                                      Gatuf::config('db_server'),    
                                      Gatuf::config('db_database')
                                      );
    return $GLOBALS['_GATUF_db'];
}
