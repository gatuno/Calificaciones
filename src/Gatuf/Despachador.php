<?php
class Gatuf_Despachador {
	
	public static function match ($req, $firstpass = true) {
		$views = $GLOBALS['_GATUF_vistas'];
		$to_match = $req->query;
		$n = count ($views);
		$i = 0;
		while ($i < $n) {
			$ctl = $views [$i];
			if (preg_match ($ctl['regex'], $to_match, $match)) {
				if (!isset ($ctl['sub'])) {
					return self::send ($req, $ctl, $match);
				} else {
					$views = $ctl['sub'];
					$i = 0;
					$n = count ($views);
					$to_match = substr ($to_match, strlen ($match[0]));
					continue;
				}
			}
			$i++;
		}
		
		if ($firstpass and substr ($req->query, -1) != '/') {
			$req->query .= '/';
			$res = self::match ($req, false);
			
			if ($res->status_code != 404) {
                Gatuf::loadFunction('Pluf_HTTP_URL_urlForView');
                $name = (isset($req->view[0]['name'])) ? 
                    $req->view[0]['name'] : 
                    $req->view[0]['model'].'::'.$req->view[0]['method'];
                $url = Gatuf_HTTP_URL_urlForView($name, array_slice($req->view[1], 1));
                return new Gatuf_HTTP_Response_Redirect($url, 301);
            }
        }
        return new Gatuf_HTTP_Response_NotFound($req);
	}
	
	public static function send($req, $ctl, $match) {
		/* Guardar la vista y el match en la petición http */
		$req->view = array ($ctl, $match);
		
		/* Cargar la clase vista controladora */
		$m = new $ctl['model']();
		/* Aquí verificar por precondiciones antes de la llamada */
		
		if (!isset ($ctl['params'])) {
			return $m->$ctl['method']($req, $match);
		} else {
			return $m->$ctl['method']($req, $match, $ctl['params']);
		}
	}
	
	public static function loadControllers($file) {
		if (file_exists($file)) {
			$GLOBALS['_GATUF_vistas'] = include $file;
			return true;
		}
		return false;
	}
}
