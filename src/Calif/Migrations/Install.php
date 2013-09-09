<?php

function Calif_Migrations_Install_setup ($params=null) {
	$models = array ('Calif_Alumno',
	                 'Calif_Carrera',
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
}

function Calif_Migrations_Install_teardown ($params=null) {
	$models = array ('Calif_Alumno',
	                 'Calif_Carrera',
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
		$schema->dropTables ();
	}
}
