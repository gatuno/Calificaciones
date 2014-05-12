<?php

function Calif_Migrations_Install_setup ($params=null) {
	$models = array ('Calif_Alumno',
	                 'Calif_Calendario',
	                 'Calif_Carrera',
	                 'Calif_Departamento',
	                 'Calif_Division',
	                 'Calif_Edificio',
	                 'Calif_Evaluacion',
	                 'Calif_GrupoEvaluacion',
	                 'Calif_Inscripcion',
	                 'Calif_Maestro',
	                 'Calif_Materia',
	                 'Calif_Nombramiento',
	                 'Calif_Salon',
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
	Calif_Migrations_Install_4Divisiones_setup ();
	Calif_Migrations_Install_5Edificios_setup ();
	Calif_Migrations_Install_6Carreras_setup ();
}

function Calif_Migrations_Install_teardown ($params=null) {
	$models = array ('Calif_Alumno',
	                 'Calif_Calendario',
	                 'Calif_Carrera',
	                 'Calif_Departamento',
	                 'Calif_Division',
	                 'Calif_Edificio',
	                 'Calif_Evaluacion',
	                 'Calif_GrupoEvaluacion',
	                 'Calif_Inscripcion',
	                 'Calif_Maestro',
	                 'Calif_Materia',
	                 'Calif_Nombramiento',
	                 'Calif_Salon',
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
	
	$materia_tabla = Gatuf::factory ('Calif_Materia')->getSqlTable ();
	$departamento_tabla = Gatuf::factory ('Calif_Departamento')->getSqlTable ();
	
	$sql = 'CREATE VIEW '.$db->pfx.'materias_view AS '."\n"
	    .'SELECT '.$materia_tabla.'.*, '.$departamento_tabla.'.descripcion as departamento_desc'."\n"
	    .'FROM '.$materia_tabla."\n"
	    .'LEFT JOIN '.$departamento_tabla.' ON '.$materia_tabla.'.departamento = '.$departamento_tabla.'.clave';
	$db->execute ($sql);
}

function Calif_Migrations_Install_1Vistas_teardown ($params = null) {
	$db = Gatuf::db ();
	
	$views = array ('materias_view',
	                );
	
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
		
		$departamento->create (); /* NO raw para que los permisos se creen automáticamente */
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

