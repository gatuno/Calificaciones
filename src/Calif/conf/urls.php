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

/* Sistema de login, y vistas base */
$ctl[] = array (
	'regex' => '#^/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/login/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'login',
	'name' => 'login_view'
);

$ctl[] = array (
	'regex' => '#^/dashboard/$#',
	'base' => $base,
	'model' => 'Calif_Views_User',
	'method' => 'dashboard',
);

$ctl[] = array (
	'regex' => '#^/logout/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'logout',
);

/* Reportes y acciones varias */
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

$ctl[] = array (
	'regex' => '#^/verificaroferta/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'verificar_oferta',
);

$ctl[] = array (
	'regex' => '#^/sobreoferta/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'sobre_oferta',
);

/* Recuperación de contraseñas */
$ctl[] = array (
	'regex' => '#^/password/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'passwordRecoveryAsk',
);

$ctl[] = array (
	'regex' => '#^/password/ik/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'passwordRecoveryInputCode',
);

$ctl[] = array (
	'regex' => '#^/password/k/(.*)/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'passwordRecovery',
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
	'regex' => '#^/alumnos/([A-Za-z]{3,5})/$#',
	'base' => $base,
	'model' => 'Calif_Views_Alumno',
	'method' => 'porCarrera',
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

$ctl[] = array (
	'regex' => '#^/alumno/(\w\d{8})/evaluar/(\d+)/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Alumno',
	'method' => 'evaluar',
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
	'regex' => '#^/materias/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Materia',
	'method' => 'porDepartamento',
);

$ctl[] = array (
	'regex' => '#^/materias/([A-Za-z]{3,5})/$#',
	'base' => $base,
	'model' => 'Calif_Views_Materia',
	'method' => 'porCarrera',
);

$ctl[] = array (
	'regex' => '#^/materia/([a-zA-Z]+\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Materia',
	'method' => 'verMateria',
);

$ctl[] = array (
	'regex' => '#^/materia/([a-zA-Z]+\d+)/evals/$#',
	'base' => $base,
	'model' => 'Calif_Views_Materia',
	'method' => 'verEval',
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

$ctl[] = array (
	'regex' => '#^/evaluacion/(\d+)/update/$#',
	'base' => $base,
	'model' => 'Calif_Views_Evaluacion',
	'method' => 'actualizarEval',
);

/* Las secciones */
$ctl[] = array (
	'regex' => '#^/secciones/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/secciones/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'agregarNrc',
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
	'regex' => '#^/seccion/(\d+)/delete/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'eliminarNrc',
);

$ctl[] = array (
	'regex' => '#^/seccion/(\d+)/timeadd/$#',
	'base' => $base,
	'model' => 'Calif_Views_Horario',
	'method' => 'agregarHora',
);

$ctl[] = array (
	'regex' => '#^/seccion/(\d+)/timedel/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Horario',
	'method' => 'eliminarHora',
);

$ctl[] = array (
	'regex' => '#^/seccion/(\d+)/timeupdate/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Horario',
	'method' => 'actualizarHora',
);

$ctl[] = array (
	'regex' => '#^/seccion/(\d+)/reclamar/([A-Za-z]{3,5})/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'reclamarNrc',
);

$ctl[] = array (
	'regex' => '#^/seccion/(\d+)/liberar/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'liberarNrc',
);

$ctl[] = array (
	'regex' => '#^/seccion/(\d+)/evaluar/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'evaluar'
);

$ctl[] = array (
	'regex' => '#^/seccion/(\d+)/matricular/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'matricular'
);

/* Los salones */
$ctl[] = array (
	'regex' => '#^/salones/$#',
	'base' => $base,
	'model' => 'Calif_Views_Salon',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/salon/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Salon',
	'method' => 'verSalon',
);

$ctl[] = array (
	'regex' => '#^/salon/(\d+)/update/$#',
	'base' => $base,
	'model' => 'Calif_Views_Salon',
	'method' => 'actualizarSalon',
);

$ctl[] = array (
	'regex' => '#^/salones/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Salon',
	'method' => 'agregarSalon',
);

$ctl[] = array (
	'regex' => '#^/salones/buscar/$#',
	'base' => $base,
	'model' => 'Calif_Views_Salon',
	'method' => 'buscarSalon',
);

/* Los maestros */
$ctl[] = array (
	'regex' => '#^/maestros/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/maestros/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'agregarMaestro',
);

$ctl[] = array (
	'regex' => '#^/maestros/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'porDepartamento',
);

$ctl[] = array (
	'regex' => '#^/maestro/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'verMaestro',
);

$ctl[] = array (
	'regex' => '#^/maestro/(\d+)/update/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'actualizarMaestro',
);

$ctl[] = array (
	'regex' => '#^/maestro/(\d+)/horario/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'verHorario',
	'params' => array ('general' => 1),
);

$ctl[] = array (
	'regex' => '#^/maestro/(\d+)/horario/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'verHorario',
	'name' => 'HorarioPorDepartamento',
);

/* Edificios */
$ctl[] = array (
	'regex' => '#^/edificios/$#',
	'base' => $base,
	'model' => 'Calif_Views_Edificio',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/edificio/(.+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Edificio',
	'method' => 'verEdificio',
);

/* Departamentos */
$ctl[] = array (
	'regex' => '#^/departamentos/$#',
	'base' => $base,
	'model' => 'Calif_Views_Departamento',
	'method' => 'index',
);

return $ctl;
