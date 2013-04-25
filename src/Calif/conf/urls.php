<?php
$base = Gatuf::config('calif_base');
$ctl = array ();

/* Bloque base:
$ctl[] = array (
	'regex' => '#^/ /$#',
	'base' => $base,
	'model' => 'Calif_',
	'method' => '',
);
*/

/* Las carreras */
$ctl[] = array(
	'regex' => '#^/carreras/$#',
	'base' => $base,
	'model' => 'Calif_Views_Carrera',
	'method' => 'index',
);

$ctl[] = array(
	'regex' => '#^/carreras/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Carrera',
	'method' => 'agregarCarrera',
);

$ctl[] = array(
	'regex' => '#^/carrera/([A-Za-z]{3,5})/$#',
	'base' => $base,
	'model' => 'Calif_Views_Carrera',
	'method' => 'verCarrera',
);

$ctl[] = array(
	'regex' => '#^/carrera/([A-Za-z]{3,5})/update/$#',
	'base' => $base,
	'model' => 'Calif_Views_Carrera',
	'method' => 'actualizarCarrera',
);

/* Los alumnos */
$ctl[] = array(
	'regex' => '#^/alumnos/$#',
	'base' => $base,
	'model' => 'Calif_Views_Alumno',
	'method' => 'index',
);

$ctl[] = array(
	'regex' => '#^/alumnos/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Alumno',
	'method' => 'agregarAlumno',
);

$ctl[] = array(
	'regex' => '#^/alumno/(\w\d{8})/$#',
	'base' => $base,
	'model' => 'Calif_Views_Alumno',
	'method' => 'verAlumno',
);

$ctl[] = array(
	'regex' => '#^/alumno/(\w\d{8})/update/$#',
	'base' => $base,
	'model' => 'Calif_Views_Alumno',
	'method' => 'actualizarAlumno',
);

/* Algunas materias */
$ctl[] = array (
	'regex' => '#^/materias/$#',
	'base' => $base,
	'model' => 'Calif_Views_Materia',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/materias/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Materia',
	'method' => 'agregarMateria',
);

$ctl[] = array (
	'regex' => '#^/materia/([a-zA-Z]+\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Materia',
	'method' => 'verMateria',
);

$ctl[] = array (
	'regex' => '#^/materia/([a-zA-Z]+\d+)/update/$#',
	'base' => $base,
	'model' => 'Calif_Views_Materia',
	'method' => 'actualizarMateria',
);

$ctl[] = array (
	'regex' => '#^/materia/([a-zA-Z]+\d+)/addeval/$#',
	'base' => $base,
	'model' => 'Calif_Views_Materia',
	'method' => 'agregarEval',
);

/* Las evaluaciones */
$ctl[] = array (
	'regex' => '#^/evaluaciones/$#',
	'base' => $base,
	'model' => 'Calif_Views_Evaluacion',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/evaluaciones/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Evaluacion',
	'method' => 'agregarEval',
);

$ctl[] = array (
	'regex' => '#^/evaluacion/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Evaluacion',
	'method' => 'verEval',
);

return $ctl;
