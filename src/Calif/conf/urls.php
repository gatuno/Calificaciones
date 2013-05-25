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

$ctl[] = array (
	'regex' => '#^/login/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'login',
	'name' => 'login_view'
);

$ctl[] = array (
	'regex' => '#^/logout/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'logout',
);

$ctl[] = array (
	'regex' => '#^/importsiiau/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'import_siiau',
);

$ctl[] = array (
	'regex' => '#^/importoferta/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'import_oferta',
);

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
	'regex' => '#^/materia/([a-zA-Z]+\d+)/addeval/(\d+)/$#',
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

/* Las secciones */
$ctl[] = array (
	'regex' => '#^/secciones/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/seccion/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'verNrc',
);

$ctl[] = array (
	'regex' => '#^/seccion/(\d+)/update/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'actualizarNrc',
);

$ctl[] = array (
	'regex' => '#^/secciones/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'agregarNrc',
);

$ctl[] = array (
	'regex' => '#^/secciones/add/([a-zA-Z]+\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'agregarNrcConMateria',
);

return $ctl;
