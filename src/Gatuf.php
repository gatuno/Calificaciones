<?php

class Gatuf {
	
	static function start($config) {
		$GLOBALS['_GATUF_starttime'] = microtime(true);
		$GLOBALS['_GATUF_uniqid'] = uniqid($GLOBALS['_GATUF_starttime'], true);
		Gatuf::loadConfig($config);
		date_default_timezone_set(Gatuf::config('time_zone', 'America/Mexico_City'));
		mb_internal_encoding('UTF-8');
		mb_regex_encoding('UTF-8');
	}
	
	static function loadConfig($config_file) {
		if (false !== ($file=Gatuf::fileExists($config_file))) {
			$GLOBALS['_GATUF_config'] = require $file;
		} else {
			throw new Exception('El archivo de configuración no existe: ' . $config_file);
		}
	}
	
	static function config($cfg, $default = '') {
		if (isset ($GLOBALS['_GATUF_config'][$cfg])) {
			return $GLOBALS['_GATUF_config'][$cfg];
		}
		return $default;
	}
	
	static function prefixconfig($pfx, $strip=false) {
		$ret = array();
		$pfx_len = strlen($pfx);
		foreach ($GLOBALS['_GATUF_config'] as $key=>$val) {
			if (0 === strpos($key, $pfx)) {
				if (!$strip) {
					$ret[$key] = $val;
				} else {
					$ret[substr($key, $pfx_len)] = $val;
				}
			}
		}
		return $ret;
	}
	
	public static function fileExists($file) {
		$file = trim ($file);
		if (!$file) {
			return false;
		}
		
		/* En el caso de que sea una ruta absoluta */
		$abs = ($file[0] == '/' || $file[0] == '\\' || $file[1] == ':');
		if ($abs && file_exists($file)) {
			return $file;
		}
		
		/* Una ruta relativa */
		$path = explode(PATH_SEPARATOR, ini_get('include_path'));
		foreach ($path as $dir) {
			$target = rtrim ($dir, '\\/').DIRECTORY_SEPARATOR.$file;
			if (file_exists($target)) {
				return $target;
			}
		}
		
		return false;
	}
	
	public static function loadClass($class) {
		if (class_exists($class, false)) {
			return;
		}
		$file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
		include $file;
		if (!class_exists($class, false)) {
			$error = 'Imposible al carga la clase: '.$class."\n".
			         'Se intentó incluir: '.$file."\n".
			         'Ruta para incluir: '.get_include_path();
			throw new Exception($error);
		}
	}
	
	public static function loadFunction($function) {
		if (function_exists($function)) {
			return;
		}
		$elts = explode ('_', $function);
		array_pop ($elts);
		$file = implode (DIRECTORY_SEPARATOR, $elts) . '.php';
		if (false !== ($file = Gatuf::fileExists($file))) {
			include $file;
		}
		if (!function_exists ($function)) {
			throw new Exception ('Imposible cargar la función: '.$function);
		}
	}
	
	/**
	* Returns a given object. 
	*
	* Loads automatically the corresponding class file if needed.
	* If impossible to get the class $model, exception is thrown.
	*
	* @param string Model to load.
	* @param mixed Extra parameters for the constructor of the model.
	*/
	public static function factory($model, $params=null) {
		if ($params !== null) {
			return new $model($params);
		}
		return new $model();
	}
	
	public static function &db($extra=null) {
		Gatuf::loadFunction('Gatuf_DB_getConnection');
		$a = Gatuf_DB_getConnection($extra);
		return $a;
    }
}

function __autoload($class_name) {
	/*try {*/
		Gatuf::loadClass($class_name);
	/*} catch (Exception $e) {
		print $e->getMessage();
		die ();
	}*/
}

class GatufErrorHandlerException extends Exception {
	public function setLine ($line) {
		$this->line = $line;
	}
	
	public function setFile ($file) {
		$this->file = $file;
	}
}

function GatufErrorHandler($code, $string, $file, $line) {
	/*#if (0 == error_reporting ()) return false;*/
	
	$exception = new GatufErrorHandlerException ($string, $code);
	$exception->setLine ($line);
	$exception->setFile ($file);
	throw $exception;
}

set_error_handler('GatufErrorHandler', error_reporting ());

function Gatuf_esc($string) {
	return str_replace(array('&', '"', '<', '>'),
	                   array('&amp;', '&quot;', '&lt;', '&gt;'),
	                   (string) $string);
}
