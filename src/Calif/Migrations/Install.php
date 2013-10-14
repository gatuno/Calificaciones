<?php

function Calif_Migrations_Install_setup ($params=null) {
	$models = array ('Calif_Alumno',
	                 'Calif_Carrera',
	                 'Calif_Calificacion',
	                 'Calif_Departamento',
	                 'Calif_Edificio',
	                 'Calif_Evaluacion',
	                 'Calif_GrupoEvaluacion',
	                 'Calif_Horario',
	                 'Calif_Maestro',
	                 'Calif_Materia',
	                 'Calif_Porcentaje',
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
	
	Calif_Migrations_Install_1Vistas_setup ();
	Calif_Migrations_Install_2GruposEval_setup ();
	Calif_Migrations_Install_3Departamentos_setup ();
	Calif_Migrations_Install_4Carreras_setup ();
	Calif_Migrations_Install_5Edificios_setup ();
}

function Calif_Migrations_Install_teardown ($params=null) {
	$models = array ('Calif_Alumno',
	                 'Calif_Carrera',
	                 'Calif_Calificacion',
	                 'Calif_Departamento',
	                 'Calif_Edificio',
	                 'Calif_Evaluacion',
	                 'Calif_GrupoEvaluacion',
	                 'Calif_Horario',
	                 'Calif_Maestro',
	                 'Calif_Materia',
	                 'Calif_Porcentaje',
	                 'Calif_Salon',
	                 'Calif_Seccion',
	                 );
	
	Calif_Migrations_Install_1Vistas_teardown ();
	
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

function Calif_Migrations_Install_1Vistas_setup ($params = null) {
	/* Crear todas las vistas necesarias */
	$db = Gatuf::db ();
	
	/* Vista de alumnos: */
	$alumno_tabla = Gatuf::factory ('Calif_Alumno')->getSqlTable ();
	$carrera_tabla = Gatuf::factory ('Calif_Carrera')->getSqlTable ();
	$sql = 'CREATE VIEW '.$db->pfx.'alumnos_view AS '."\n"
	    .'SELECT '.$alumno_tabla.'.*, '.$carrera_tabla.'.descripcion as carrera_desc'."\n"
	    .'FROM '.$alumno_tabla."\n"
	    .'LEFT JOIN '.$carrera_tabla.' ON '.$alumno_tabla.'.carrera = '.$carrera_tabla.'.clave';
	$db->execute ($sql);
	
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
	
	$views = array ('alumnos_view',
	                'materias_view',
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

function Calif_Migrations_Install_4Carreras_setup ($params = null) {
	$carrera_model = new Calif_Carrera ();
	
	$carreras = array ('BIM' => 'Ingeniería en Biomédica',
	                   'CEL' => 'Ingeniería en Electrónica y Comunicaciones',
	                   'CIV' => 'Ingeniería Civil',
	                   'COM' => 'Ingenieria en Computación',
	                   'FIS' => 'Licenciatura en Física',
	                   'INBI' => 'Ingeniería en Biomédica (Nueva)',
	                   'INCE' => 'Ingeniería en Electrónica y Comunicaciones (Nueva)',
	                   'INCO' => 'Ingeniería en Computación (Nueva)',
	                   'IND' => 'Ingeniería Industrial',
	                   'INF' => 'Licenciatura en Informática',
	                   'INNI' => 'Ingeniería en Informática (Nueva)',
	                   'IQU' => 'Ingeniería Química',
	                   'LIFI' => 'Licenciatura en Física (Nueva)',
	                   'LIMA' => 'Licenciatura en Matemáticas (Nueva)',
	                   'MAT' => 'Licenciatura en Matemáticas',
	                   'MEL' => 'Ingeniería Mecánica Eléctrica',
	                   'QFB' => 'Licenciatura en Químico Farmacobiólogo',
	                   'QUI' => 'Licenciatura en Química',
	                   'TOP' => 'Ingeniería en Topografía');
	
	foreach ($carreras as $clave => $descripcion) {
		$carrera_model->clave = $clave;
		$carrera_model->descripcion = $descripcion;
		$carrera_model->color = 0;
		
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
