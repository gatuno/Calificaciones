<?php

class Calif_Calendario extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'calendarios';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'clave';
		
		$this->_a['cols'] = array (
			'clave' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 6,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 20,
			),
			'oculto' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
		);
	}
	
	public function postSave ($create = true) {
		if ($create) {
			/* Crear todos las tablas que cambian entre semestre y semestre */
			$models = array ('Calif_Calificacion',
			                 'Calif_Horario',
			                 'Calif_NumeroPuesto',
			                 'Calif_Porcentaje',
			                 'Calif_Promedio',
			                 'Calif_Seccion',
			                 );
			
			$GLOBALS['CAL_ACTIVO'] = $this->clave;
			
			$db = Gatuf::db ();
			$schema = new Gatuf_DB_Schema ($db);
			foreach ($models as $model) {
				$schema->model = new $model ();
				$schema->createTables ();
			}
	
			foreach ($models as $model) {
				$schema->model = new $model ();
				$schema->createConstraints ();
			}
			
			Calif_Calendario_installTriggersSetup ();
			Calif_Calendario_installVistasSetup ();
		}
	}
}

function Calif_Calendario_getDefault () {
	return isset ($GLOBALS['CAL_ACTIVO']) ? $GLOBALS['CAL_ACTIVO'] : '';
}

function Calif_Calendario_installTriggersSetup ($params = null) {
	$db = Gatuf::db ();
	
	$seccion_tabla = Gatuf::factory ('Calif_Seccion')->getSqlTable ();
	$porcentaje_tabla = Gatuf::factory ('Calif_Porcentaje')->getSqlTable ();
	$calificacion_tabla = Gatuf::factory ('Calif_Calificacion')->getSqlTable ();
	$promedio_tabla = Gatuf::factory ('Calif_Promedio')->getSqlTable ();
	
	$hay = array (strtolower ('Calif_Alumno'), strtolower('Calif_Seccion'));
	// Calcular la base de datos que contiene la relación M-N
	$dbname = $db->dbname;
	$calpfx = Calif_Calendario_getDefault ();
	$dbpfx = $db->pfx.$calpfx;
	sort ($hay);
	$t_asso = $dbname.'.'.$dbpfx.$hay[0].'_'.$hay[1].'_assoc';
	
	$sql = 'CREATE TRIGGER '.$dbname.'.'.$dbpfx.'insert_alumno AFTER INSERT ON '.$t_asso."\n"
	    .'FOR EACH ROW BEGIN'."\n"
	    .'INSERT INTO '.$calificacion_tabla.' (nrc, alumno, evaluacion, valor)'."\n"
	    .'SELECT NEW.calif_seccion_nrc, NEW.calif_alumno_codigo, P.evaluacion, NULL FROM '.$seccion_tabla.' AS S'."\n"
	    .'INNER JOIN '.$porcentaje_tabla.' AS P ON S.materia = P.materia WHERE S.nrc = NEW.calif_seccion_nrc;'."\n"
	    .'UPDATE '.$promedio_tabla.' AS PP'."\n"
	    .'INNER JOIN ('."\n"
	    .'SELECT evaluacion,'."\n"
	    .'AVG (GREATEST (COALESCE (valor, 0), 0)) AS promedio'."\n"
	    .'FROM '.$calificacion_tabla."\n"
	    .'GROUP BY evaluacion'."\n"
	    .') AS sub'."\n"
	    .'ON PP.evaluacion = sub.evaluacion'."\n"
	    .'SET PP.promedio = sub.promedio'."\n"
	    .'WHERE PP.nrc = NEW.calif_seccion_nrc;'."\n"
	    .'END';
	$db->execute ($sql);
	
	$sql = 'CREATE TRIGGER '.$dbname.'.'.$dbpfx.'delete_alumno AFTER DELETE ON '.$t_asso."\n"
	    .' FOR EACH ROW BEGIN'."\n"
	    .'DELETE FROM '.$calificacion_tabla.' WHERE Alumno = OLD.calif_alumno_codigo AND Nrc = OLD.calif_seccion_nrc;'."\n"
	    .'UPDATE '.$promedio_tabla.' AS PP'."\n"
	    .'INNER JOIN ('."\n"
	    .'SELECT evaluacion,'."\n"
	    .'AVG (GREATEST (COALESCE (valor, 0), 0)) AS promedio'."\n"
	    .'FROM '.$calificacion_tabla."\n"
	    .'GROUP BY evaluacion'."\n"
	    .') AS sub'."\n"
	    .'ON PP.evaluacion = sub.evaluacion'."\n"
	    .'SET PP.promedio = sub.promedio'."\n"
	    .'WHERE PP.nrc = OLD.calif_seccion_nrc;'."\n"
	    .'END';
	$db->execute ($sql);
	
	$sql = 'CREATE TRIGGER '.$dbname.'.'.$dbpfx.'insert_evaluacion AFTER INSERT ON '.$porcentaje_tabla."\n"
	    .' FOR EACH ROW BEGIN'."\n"
	    .'INSERT INTO '.$calificacion_tabla.' (nrc, alumno, evaluacion, valor)'."\n"
	    .'SELECT G.calif_seccion_nrc, G.calif_alumno_codigo, NEW.evaluacion, NULL'."\n"
	    .'FROM '.$t_asso.' AS G'."\n"
	    .'INNER JOIN '.$seccion_tabla.' AS S ON G.calif_seccion_nrc = S.nrc'."\n"
	    .'WHERE S.materia = NEW.materia;'."\n"
	    .'INSERT INTO '.$promedio_tabla.' (nrc, evaluacion, promedio)'."\n"
	    .'SELECT S.nrc, NEW.evaluacion, 0.0'."\n"
	    .'FROM '.$seccion_tabla.' AS S'."\n"
	    .'WHERE S.materia = NEW.materia;'."\n"
	    .'END';
	$db->execute ($sql);
	
	$sql = 'CREATE TRIGGER '.$dbname.'.'.$dbpfx.'delete_evaluacion AFTER DELETE ON '.$porcentaje_tabla."\n"
	    .' FOR EACH ROW BEGIN'."\n"
	    .'DELETE C FROM '.$calificacion_tabla.' AS C, '.$seccion_tabla.' AS S WHERE C.nrc = S.nrc'."\n"
	    .'AND S.materia = OLD.materia AND C.evaluacion = OLD.evaluacion;'."\n"
	    .'DELETE P FROM '.$promedio_tabla.' AS P, '.$seccion_tabla.' AS S'."\n"
	    .'WHERE P.nrc = S.nrc AND S.materia = OLD.materia AND P.evaluacion = OLD.evaluacion;'."\n"
	    .'END';
	$db->execute ($sql);
	
	$sql = 'CREATE TRIGGER '.$dbname.'.'.$dbpfx.'update_promedios AFTER UPDATE ON '.$calificacion_tabla."\n"
	    .' FOR EACH ROW BEGIN'."\n"
	    .'UPDATE '.$promedio_tabla.' as P'."\n"
	    .'SET P.promedio = (SELECT AVG(GREATEST(COALESCE(C.valor,0),0)) FROM '.$calificacion_tabla.' as C WHERE C.evaluacion = NEW.evaluacion AND nrc = NEW.nrc)'."\n"
	    .'WHERE P.nrc = NEW.nrc and P.evaluacion = NEW.evaluacion;'."\n"
	    .'END';
	$db->execute ($sql);
}

function Calif_Calendario_installTriggersTeardown ($params = null) {
	$db = Gatuf::db ();
	$calpfx = Calif_Calendario_getDefault ();
	$dbname = $db->dbname;
	$dbpfx = $db->pfx.$calpfx;
	
	$triggers = array ('insert_alumno',
	                   'delete_alumno',
	                   'insert_evaluacion',
	                   'delete_evaluacion',
	                   'update_promedios');
	
	foreach ($triggers as $trigger) {
		$sql = 'DROP TRIGGER '.$dbname.'.'.$dbpfx.$trigger;
		
		$db->execute ($sql);
	}
}

function Calif_Calendario_installVistasSetup ($params = null) {
	/* Crear todas las vistas necesarias */
	$db = Gatuf::db ();
	$calpfx = Calif_Calendario_getDefault ();
	$dbpfx = $db->pfx.$calpfx;
	
	$dbname = $db->dbname;
	
	$seccion_tabla = Gatuf::factory ('Calif_Seccion')->getSqlTable ();
	$horario_tabla = Gatuf::factory ('Calif_Horario')->getSqlTable ();
	
	$materia_tabla = Gatuf::factory ('Calif_Materia')->getSqlTable ();
	$departamento_tabla = Gatuf::factory ('Calif_Departamento')->getSqlTable ();
	$maestro_tabla = Gatuf::factory ('Calif_Maestro')->getSqlTable ();
	
	$sql = 'CREATE VIEW '.$dbname.'.'.$dbpfx.'secciones_view AS '."\n"
	    .'SELECT '.$seccion_tabla.'.*, '.$materia_tabla.'.descripcion as materia_desc, '.$materia_tabla.'.departamento as materia_departamento, '.$maestro_tabla.'.nombre as maestro_nombre, '.$maestro_tabla.'.apellido as maestro_apellido'."\n"
	    .'FROM '.$seccion_tabla."\n"
	    .'LEFT JOIN '.$materia_tabla.' ON '.$seccion_tabla.'.materia = '.$materia_tabla.'.clave'."\n"
	    .'LEFT JOIN '.$maestro_tabla.' ON '.$seccion_tabla.'.maestro = '.$maestro_tabla.'.codigo';
	$db->execute ($sql);
	
	/* Vista de horarios */
	$salon_tabla = Gatuf::factory ('Calif_Salon')->getSqlTable ();
	$carrera_tabla = Gatuf::factory ('Calif_Carrera')->getSqlTable ();
	
	$sql = 'CREATE VIEW '.$dbname.'.'.$dbpfx.'horarios_view AS '."\n"
	     .'SELECT '.$horario_tabla.'.*, '.$salon_tabla.'.aula AS salon_aula, '.$salon_tabla.'.edificio AS salon_edificio,'."\n"
	     .$seccion_tabla.'.maestro AS seccion_maestro, '.$seccion_tabla.'.asignacion AS seccion_asignacion, '.$carrera_tabla.'.color as seccion_asignacion_color'."\n"
	     .'FROM '.$horario_tabla."\n"
	     .'LEFT JOIN '.$salon_tabla.' ON '.$horario_tabla.'.salon = '.$salon_tabla.'.id'."\n"
	     .'LEFT JOIN '.$seccion_tabla.' ON '.$horario_tabla.'.nrc = '.$seccion_tabla.'.nrc'."\n"
	     .'LEFT JOIN '.$carrera_tabla.' ON '.$seccion_tabla.'.asignacion = '.$carrera_tabla.'.clave';
	$db->execute ($sql);
	
	/* Vista Maestros-Departamentos */
	
	$sql = 'CREATE VIEW '.$dbname.'.'.$dbpfx.'maestros_departamentos AS '."\n"
	     .'SELECT '.$maestro_tabla.'.*, '.$materia_tabla.'.departamento as departamento'."\n"
	     .'FROM '.$maestro_tabla."\n"
	     .'INNER JOIN '.$seccion_tabla.' ON '.$seccion_tabla.'.maestro = '.$maestro_tabla.'.codigo'."\n"
	     .'LEFT JOIN '.$materia_tabla.' ON '.$seccion_tabla.'.materia = '.$materia_tabla.'.clave'."\n"
	     .'GROUP BY '.$maestro_tabla.'.codigo,'.$materia_tabla.'.departamento'."\n";
	$db->execute ($sql);
}

