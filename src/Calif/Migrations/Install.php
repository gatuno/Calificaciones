<?php

function Calif_Migrations_Install_setup ($params=null) {
	$models = array ('Calif_Alumno',
	                 'Calif_Carrera',
	                 'Calif_Calificacion',
	                 'Calif_Departamento',
	                 'Calif_Division',
	                 'Calif_Edificio',
	                 'Calif_Evaluacion',
	                 'Calif_GrupoEvaluacion',
	                 'Calif_Horario',
	                 'Calif_Maestro',
	                 'Calif_Materia',
	                 'Calif_Nombramiento',
	                 'Calif_NumeroPuesto',
	                 'Calif_Porcentaje',
	                 'Calif_Promedio',
	                 'Calif_Salon',
	                 'Calif_Seccion',
	                 );
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
	Calif_Migrations_Install_Triggers_setup ();
	
	Calif_Migrations_Install_1Vistas_setup ();
	Calif_Migrations_Install_2GruposEval_setup ();
	Calif_Migrations_Install_3Departamentos_setup ();
	Calif_Migrations_Install_4Divisiones_setup ();
	Calif_Migrations_Install_5Edificios_setup ();
	Calif_Migrations_Install_6Carreras_setup ();
}

function Calif_Migrations_Install_teardown ($params=null) {
	$models = array ('Calif_Alumno',
	                 'Calif_Carrera',
	                 'Calif_Calificacion',
	                 'Calif_Departamento',
	                 'Calif_Division',
	                 'Calif_Edificio',
	                 'Calif_Evaluacion',
	                 'Calif_GrupoEvaluacion',
	                 'Calif_Horario',
	                 'Calif_Maestro',
	                 'Calif_Materia',
	                 'Calif_Nombramiento',
	                 'Calif_NumeroPuesto',
	                 'Calif_Porcentaje',
	                 'Calif_Promedio',
	                 'Calif_Salon',
	                 'Calif_Seccion',
	                 );
	
	Calif_Migrations_Install_1Vistas_teardown ();
	Calif_Migrations_Install_Triggers_teardown ();
	
	$db = Gatuf::db ();
	$schema = new Gatuf_DB_Schema ($db);
	
	foreach ($models as $model) {
		$schema->model = new $model ();
		$schema->dropConstraints();
	}
	
	foreach ($models as $model) {
		$schema->model = new $model ();
		$schema->dropTables ();
	}
}

function Calif_Migrations_Install_Triggers_setup ($params = null) {
	$db = Gatuf::db ();
	
	$hay = array (strtolower ('Calif_Alumno'), strtolower('Calif_Seccion'));
	sort ($hay);
	$t_asso = $db->pfx.$hay[0].'_'.$hay[1].'_assoc';
	
	$seccion_tabla = Gatuf::factory ('Calif_Seccion')->getSqlTable ();
	$porcentaje_tabla = Gatuf::factory ('Calif_Porcentaje')->getSqlTable ();
	$calificacion_tabla = Gatuf::factory ('Calif_Calificacion')->getSqlTable ();
	$promedio_tabla = Gatuf::factory ('Calif_Promedio')->getSqlTable ();
	
	$sql = 'CREATE TRIGGER '.$db->pfx.'insert_alumno AFTER INSERT ON '.$t_asso."\n"
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
	
	$sql = 'CREATE TRIGGER '.$db->pfx.'delete_alumno AFTER DELETE ON '.$t_asso."\n"
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
	
	$sql = 'CREATE TRIGGER '.$db->pfx.'insert_evaluacion AFTER INSERT ON '.$porcentaje_tabla."\n"
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
	
	$sql = 'CREATE TRIGGER '.$db->pfx.'delete_evaluacion AFTER DELETE ON '.$porcentaje_tabla."\n"
	    .' FOR EACH ROW BEGIN'."\n"
	    .'DELETE C FROM '.$calificacion_tabla.' AS C, '.$seccion_tabla.' AS S WHERE C.nrc = S.nrc'."\n"
	    .'AND S.materia = OLD.materia AND C.evaluacion = OLD.evaluacion;'."\n"
	    .'DELETE P FROM '.$promedio_tabla.' AS P, '.$seccion_tabla.' AS S'."\n"
	    .'WHERE P.nrc = S.nrc AND S.materia = OLD.materia AND P.evaluacion = OLD.evaluacion;'."\n"
	    .'END';
	$db->execute ($sql);
	
	$sql = 'CREATE TRIGGER '.$db->pfx.'update_promedios AFTER UPDATE ON '.$calificacion_tabla."\n"
	    .' FOR EACH ROW BEGIN'."\n"
	    .'UPDATE '.$promedio_tabla.' as P'."\n"
	    .'SET P.promedio = (SELECT AVG(GREATEST(COALESCE(C.valor,0),0)) FROM '.$calificacion_tabla.' as C WHERE C.evaluacion = NEW.evaluacion AND nrc = NEW.nrc)'."\n"
	    .'WHERE P.nrc = NEW.nrc and P.evaluacion = NEW.evaluacion;'."\n"
	    .'END';
	$db->execute ($sql);
}

function Calif_Migrations_Install_Triggers_teardown ($params = null) {
	$db = Gatuf::db ();
	$triggers = array ('insert_alumno',
	                   'delete_alumno',
	                   'insert_evaluacion',
	                   'delete_evaluacion',
	                   'update_promedios');
	
	foreach ($triggers as $trigger) {
		$sql = 'DROP TRIGGER '.$db->pfx.$trigger;
		
		$db->execute ($sql);
	}
}

function Calif_Migrations_Install_1Vistas_setup ($params = null) {
	/* Crear todas las vistas necesarias */
	$db = Gatuf::db ();
	
	$materia_tabla = Gatuf::factory ('Calif_Materia')->getSqlTable ();
	$departamento_tabla = Gatuf::factory ('Calif_Departamento')->getSqlTable ();
	
	$sql = 'CREATE VIEW '.$db->pfx.'materias_view AS '."\n"
	    .'SELECT '.$materia_tabla.'.*, '.$departamento_tabla.'.descripcion as departamento_desc'."\n"
	    .'FROM '.$materia_tabla."\n"
	    .'LEFT JOIN '.$departamento_tabla.' ON '.$materia_tabla.'.departamento = '.$departamento_tabla.'.clave';
	$db->execute ($sql);
	
	$maestro_tabla = Gatuf::factory ('Calif_Maestro')->getSqlTable ();
	$seccion_tabla = Gatuf::factory ('Calif_Seccion')->getSqlTable ();
	
	$sql = 'CREATE VIEW '.$db->pfx.'secciones_view AS '."\n"
	    .'SELECT '.$seccion_tabla.'.*, '.$materia_tabla.'.descripcion as materia_desc, '.$materia_tabla.'.departamento as materia_departamento, '.$maestro_tabla.'.nombre as maestro_nombre, '.$maestro_tabla.'.apellido as maestro_apellido'."\n"
	    .'FROM '.$seccion_tabla."\n"
	    .'LEFT JOIN '.$materia_tabla.' ON '.$seccion_tabla.'.materia = '.$materia_tabla.'.clave'."\n"
	    .'LEFT JOIN '.$maestro_tabla.' ON '.$seccion_tabla.'.maestro = '.$maestro_tabla.'.codigo';
	$db->execute ($sql);
	
	/* Vista de horarios */
	$horario_tabla = Gatuf::factory ('Calif_Horario')->getSqlTable ();
	$salon_tabla = Gatuf::factory ('Calif_Salon')->getSqlTable ();
	$carrera_tabla = Gatuf::factory ('Calif_Carrera')->getSqlTable ();
	
	$sql = 'CREATE VIEW '.$db->pfx.'horarios_view AS '."\n"
	     .'SELECT '.$horario_tabla.'.*, '.$salon_tabla.'.aula AS salon_aula, '.$salon_tabla.'.edificio AS salon_edificio,'."\n"
	     .$seccion_tabla.'.maestro AS seccion_maestro, '.$seccion_tabla.'.asignacion AS seccion_asignacion, '.$carrera_tabla.'.color as seccion_asignacion_color'."\n"
	     .'FROM '.$horario_tabla."\n"
	     .'LEFT JOIN '.$salon_tabla.' ON '.$horario_tabla.'.salon = '.$salon_tabla.'.id'."\n"
	     .'LEFT JOIN '.$seccion_tabla.' ON '.$horario_tabla.'.nrc = '.$seccion_tabla.'.nrc'."\n"
	     .'LEFT JOIN '.$carrera_tabla.' ON '.$seccion_tabla.'.asignacion = '.$carrera_tabla.'.clave';
	$db->execute ($sql);
	
	/* Vista Maestros-Departamentos */
	
	$sql = 'CREATE VIEW '.$db->pfx.'maestros_departamentos AS '."\n"
	     .'SELECT '.$maestro_tabla.'.*, '.$materia_tabla.'.departamento as departamento'."\n"
	     .'FROM '.$maestro_tabla."\n"
	     .'INNER JOIN '.$seccion_tabla.' ON '.$seccion_tabla.'.maestro = '.$maestro_tabla.'.codigo'."\n"
	     .'LEFT JOIN '.$materia_tabla.' ON '.$seccion_tabla.'.materia = '.$materia_tabla.'.clave'."\n"
	     .'GROUP BY '.$maestro_tabla.'.codigo,'.$materia_tabla.'.departamento'."\n";
	$db->execute ($sql);
}

function Calif_Migrations_Install_1Vistas_teardown ($params = null) {
	$db = Gatuf::db ();
	
	$views = array ('materias_view',
	                'secciones_view',
	                'horarios_view',
	                'maestros_departamentos');
	
	foreach ($views as $view) {
		$sql = 'DROP VIEW '.$db->pfx.$view;
		
		$db->execute ($sql);
	}
}

function Calif_Migrations_Install_2GruposEval_setup ($params = null) {
	$geval = new Calif_GrupoEvaluacion ();
	
	/* Crear las tres primeros y necesarios grupos de evaluacion */
	$grupos = array (1 => 'Ordinario',
	                 2 => 'Extraordinario',
	                 3 => 'Verano');
	
	foreach ($grupos as $id => $descripcion) {
		$geval->id = $id;
		$geval->descripcion = $descripcion;
		
		$geval->create ();
	}
}

function Calif_Migrations_Install_3Departamentos_setup ($params = null) {
	$departamento = new Calif_Departamento ();
	
	$depas = array (0 => 'Sin departamento',
	                1360 => 'Departamento de Farmacobiología',
	                1370 => 'Departamento de Física',
	                1390 => 'Departamento de Matemáticas',
	                1400 => 'Departamento de Química',
	                1420 => 'Departamento de Ingeniería Civil y Topografía',
	                1440 => 'Departamento de Ingeniería Industrial',
	                1450 => 'Departamento de Mecánica Eléctrica',
	                1460 => 'Departamento de Ingeniería de Proyectos',
	                1470 => 'Departamento de Ingeniería Química',
	                1480 => 'Departamento de Madera, Celulosa y Papel',
	                1500 => 'Departamento de Ciencias Computacionales',
	                1510 => 'Departamento de Electrónica',
	                1512 => 'Departamento de Biomédica');
	foreach ($depas as $clave => $descripcion) {
		$departamento->clave = $clave;
		$departamento->descripcion = $descripcion;
		
		$departamento->create (true);
	}
}

function Calif_Migrations_Install_6Carreras_setup ($params = null) {
	$carrera_model = new Calif_Carrera ();
	
	$carreras = array ('BIM' => array ('Ingeniería en Biomédica', 'DIVEC'),
	                   'CEL' => array ('Ingeniería en Electrónica y Comunicaciones', 'DIVEC'),
	                   'CIV' => array ('Ingeniería Civil', 'DIVING'),
	                   'COM' => array ('Ingenieria en Computación', 'DIVEC'),
	                   'DCEC' => array ('Doctorado en Ciencias de la Electrónica y la Computación', 'DIVEC'),
	                   'FIS' => array ('Licenciatura en Física', 'DIVBASICAS'),
	                   'INBI' => array ('Ingeniería en Biomédica (Nueva)', 'DIVEC'),
	                   'INCE' => array ('Ingeniería en Electrónica y Comunicaciones (Nueva)', 'DIVEC'),
	                   'INCO' => array ('Ingeniería en Computación (Nueva)', 'DIVEC'),
	                   'IND' => array ('Ingeniería Industrial', 'DIVING'),
	                   'INDU' => array ('Ingeniería Industrial (Nueva)', 'DIVING'),
	                   'INF' => array ('Licenciatura en Informática', 'DIVEC'),
	                   'INME' => array ('Ingeniería Mecánica Eléctrica (Nueva)', 'DIVING'),
	                   'INNI' => array ('Ingeniería en Informática (Nueva)', 'DIVEC'),
	                   'INQU' => array ('Ingeniería Química (Nueva)', 'DIVING'),
	                   'IQU' => array ('Ingeniería Química', 'DIVING'),
	                   'LIFI' => array ('Licenciatura en Física (Nueva)', 'DIVBASICAS'),
	                   'LIMA' => array ('Licenciatura en Matemáticas (Nueva)', 'DIVBASICAS'),
	                   'LINA' => array ('Licenciatura en Ingeniería en Alimentos y Biotecnología', 'DIVING'),
	                   'LQFB' => array ('Licenciatura en Quimico Farmaceutico Biologo (Nueva)', 'DIVBASICAS'),
	                   'LQUI' => array ('Licenciatura en Química (Nueva)', 'DIVBASICAS'),
	                   'MAT' => array ('Licenciatura en Matemáticas', 'DIVBASICAS'),
	                   'MEL' => array ('Ingeniería Mecánica Eléctrica', 'DIVING'),
	                   'MIEC' => array ('Maestría en Ciencias en Ingeniería Electrónica y Computación', 'DIVBASICAS'),
	                   'QFB' => array ('Licenciatura en Químico Farmacobiólogo', 'DIVBASICAS'),
	                   'QUI' => array ('Licenciatura en Química', 'DIVBASICAS'),
	                   'TOP' => array ('Ingeniería en Topografía', 'DIVING')
	                   );
	
	$division = new Calif_Division ();
	
	foreach ($carreras as $clave => $data) {
		$carrera_model->clave = $clave;
		$carrera_model->descripcion = $data[0];
		$carrera_model->color = 0;
		
		$sql = new Gatuf_SQL ('abreviacion=%s', $data[1]);
		$div = $division->getOne (array ('filter' => $sql->gen ()));
		
		$carrera_model->division = $div;
		
		$carrera_model->create (); /* NO raw para que los permisos se creen automáticamente */
	}
}

function Calif_Migrations_Install_5Edificios_setup ($params = null) {
	$edificio_model = new Calif_Edificio ();
	
	$edificios = array ('DEDE' => 'Módulo E',
	                    'DEDF' => 'Módulo F',
	                    'DEDG' => 'Módulo G',
	                    'DEDI' => 'Módulo I',
	                    'DEDK' => 'Módulo K',
	                    'DEDL' => 'Módulo L',
	                    'DEDM' => 'Módulo M',
	                    'DEDN' => 'Módulo N',
	                    'DEDP' => 'Módulo P',
	                    'DEDQ' => 'Módulo Q',
	                    'DEDR' => 'Módulo R',
	                    'DEDS' => 'Módulo S',
	                    'DEDT' => 'Módulo T',
	                    'DEDU' => 'Módulo U',
	                    'DEDV' => 'Módulo V',
	                    'DEDW' => 'Módulo W',
	                    'DEDX' => 'Módulo X',
	                    'DNONE' => 'Sin Edificio',
	                    'DUCT1' => 'Edificio Alfa',
	                    'DUCT2' => 'Edificio Beta');
	
	foreach ($edificios as $clave => $descripcion) {
		$edificio_model->clave = $clave;
		$edificio_model->descripcion = $descripcion;
		
		$edificio_model->create (true);
	}
}

function Calif_Migrations_Install_4Divisiones_setup ($params = null) {
	$division_model = new Calif_Division ();
	
	$divisiones = array (
		'DIVEC' => array ('Electrónica y Computación', 'División de Electrónica y Computación'),
		'DIVING' => array ('Ingenierías', 'División de Ingenierías'),
		'DIVBASICAS' => array ('Ciencias Básicas', 'División de Ciencias Básicas')
	);
	
	foreach ($divisiones as $abrev => $data) {
		$division_model->abreviacion = $abrev;
		$division_model->nombre = $data[0];
		$division_model->descripcion = $data[1];
		
		$division_model->create (true);
	}
}

