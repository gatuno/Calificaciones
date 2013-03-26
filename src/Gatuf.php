<?php

class Gatuf {
	
	static function start($config) {
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
}

function __autoload($class_name) {
	try {
		Gatuf::loadClass($class_name);
	} catch (Exception $e) {
		print $e->getMessage();
		die ();
	}
}

function Gatuf_esc($string) {
	return str_replace(array('&', '"', '<', '>'),
	                   array('&amp;', '&quot;', '&lt;', '&gt;'),
	                   (string) $string);
}
