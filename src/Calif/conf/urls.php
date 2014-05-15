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
	'regex' => '#^/calif/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'calif',
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

/* Calendarios */
$ctl[] = array (
	'regex' => '#^/calendario/$#',
	'base' => $base,
	'model' => 'Calif_Views_Calendario',
	'method' => 'cambiarCalendario',
);

$ctl[] = array (
	'regex' => '#^/calendarios/$#',
	'base' => $base,
	'model' => 'Calif_Views_Calendario',
	'method' => 'index',
);

/* Reportes y acciones varias */
$ctl[] = array (
	'regex' => '#^/importsiiau/$#',
	'base' => $base,
	'model' => 'Calif_Views',
	'method' => 'import_siiau',
);

$ctl[] = array (
	'regex' => '#^/calendarios/importoferta/$#',
	'base' => $base,
	'model' => 'Calif_Views_Calendario',
	'method' => 'import_oferta',
);

$ctl[] = array (
	'regex' => '#^/sistema/$#',
	'base' => $base,
	'model' => 'Calif_Views_System',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/sistema/importar_inventados/$#',
	'base' => $base,
	'model' => 'Calif_Views_System',
	'method' => 'importarNrcInventado',
);

$ctl[] = array (
	'regex' => '#^/sistema/auditoria/severa/$#',
	'base' => $base,
	'model' => 'Calif_Views_System',
	'method' => 'auditoriaSevera',
	'name' => 'auditoriaSeveraDepartamento',
	'params' => array ('car' => false),
);

$ctl[] = array (
	'regex' => '#^/sistema/auditoria/severa/carrera/$#',
	'base' => $base,
	'model' => 'Calif_Views_System',
	'method' => 'auditoriaSevera',
	'name' => 'auditoriaSeveraCarrera',
	'params' => array ('car' => true),
);

$ctl[] = array (
	'regex' => '#^/sistema/auditoria/horaria/$#',
	'base' => $base,
	'model' => 'Calif_Views_System',
	'method' => 'auditoriaHoraria',
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

$ctl[] = array(
	'regex' => '#^/password/change/$#',
	'base' => $base,
	'model' => 'Calif_Views_Usuario',
	'method' => 'passwordChange',
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

/*$ctl[] = array(
	'regex' => '#^/alumnos/([A-Za-z]{3,5})/$#',
	'base' => $base,
	'model' => 'Calif_Views_Alumno',
	'method' => 'porCarrera',
);*/

$ctl[] = array(
	'regex' => '#^/alumno/#',
	'base' => $base,
	'sub' => array (
		array(
			'regex' => '#^(\w\d{8})/$#',
			'base' => $base,
			'model' => 'Calif_Views_Alumno',
			'method' => 'verAlumno',
		),
		array(
			'regex' => '#^(\w\d{8})/update/$#',
			'base' => $base,
			'model' => 'Calif_Views_Alumno',
			'method' => 'actualizarAlumno',
		),
		array (
			'regex' => '#^(\w\d{8})/evaluar/(\d+)/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Alumno',
			'method' => 'evaluar',
		)
	)
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
	'regex' => '#^/materias/nofiltro/([cd])/$#',
	'base' => $base,
	'model' => 'Calif_Views_Materia',
	'method' => 'eliminarFiltro',
);

$ctl[] = array (
	'regex' => '#^/materia/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^([a-zA-Z]+\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Materia',
			'method' => 'verMateria',
		),
		array (
			'regex' => '#^([a-zA-Z]+\d+)/evals/$#',
			'base' => $base,
			'model' => 'Calif_Views_Materia',
			'method' => 'verEval',
		),
		array (
			'regex' => '#^([a-zA-Z]+\d+)/update/$#',
			'base' => $base,
			'model' => 'Calif_Views_Materia',
			'method' => 'actualizarMateria',
		),
		array (
			'regex' => '#^([a-zA-Z]+\d+)/addeval/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Materia',
			'method' => 'agregarEval',
		),
		array (
			'regex' => '#^([a-zA-Z]+\d+)/addcarrera/$#',
			'base' => $base,
			'model' => 'Calif_Views_Materia',
			'method' => 'agregarACarrera',
		)
	)
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
	'regex' => '#^/secciones/f/i/([0-9])/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'porDivision',
);

$ctl[] = array (
	'regex' => '#^/secciones/f/d/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'porDepartamento',
);

$ctl[] = array (
	'regex' => '#^/secciones/f/c/([A-Za-z]{3,5})/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'porCarrera',
);

$ctl[] = array (
	'regex' => '#^/secciones/f/n/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'porNoAsignadas',
);

$ctl[] = array (
	'regex' => '#^/secciones/error/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'errorHoras',
);

$ctl[] = array (
	'regex' => '#^/secciones/nofiltro/([cdin])/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'eliminarFiltro',
);

$ctl[] = array (
	'regex' => '#^/seccion/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'verNrc',
		),
		array (
			'regex' => '#^(\d+)/update/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'actualizarNrc',
		),
		array (
			'regex' => '#^(\d+)/delete/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'eliminarNrc',
		),
		array (
			'regex' => '#^(\d+)/timeadd/$#',
			'base' => $base,
			'model' => 'Calif_Views_Horario',
			'method' => 'agregarHora',
		),
		array (
			'regex' => '#^(\d+)/timedel/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Horario',
			'method' => 'eliminarHora',
		),
		array (
			'regex' => '#^(\d+)/timeupdate/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Horario',
			'method' => 'actualizarHora',
		),
		array (
			'regex' => '#^(\d+)/reclamar/([A-Za-z]{3,5})/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'reclamarNrc',
		),
		array (
			'regex' => '#^(\d+)/liberar/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'liberarNrc',
		),
		array (
			'regex' => '#^(\d+)/evaluar/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'evaluar'
		),
		array (
			'regex' => '#^(\d+)/matricular/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'matricular'
		)
	)
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

/* Los profesores */
$ctl[] = array (
	'regex' => '#^/profesores/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/profesores/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'agregarMaestro',
);

$ctl[] = array (
	'regex' => '#^/profesores/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'porDepartamento',
);

$ctl[] = array (
	'regex' => '#^/profesores/nofiltro/$#',
	'base' => $base,
	'model' => 'Calif_Views_Maestro',
	'method' => 'eliminarFiltro',
);

$ctl[] = array (
	'regex' => '#^/profesor/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Maestro',
			'method' => 'verMaestro',
		),
		array (
			'regex' => '#^(\d+)/update/$#',
			'base' => $base,
			'model' => 'Calif_Views_Maestro',
			'method' => 'actualizarMaestro',
		),
		array (
			'regex' => '#^(\d+)/horario/$#',
			'base' => $base,
			'model' => 'Calif_Views_Maestro',
			'method' => 'verHorario',
		),
		array (
			'regex' => '#^(\d+)/horario/PDF/$#',
			'base' => $base,
			'model' => 'Calif_Views_Maestro',
			'method' => 'verHorarioPDF',
			'params' => array ('general' => 1),
		),
		array (
			'regex' => '#^(\d+)/horario/(\d+)/PDF/$#',
			'base' => $base,
			'model' => 'Calif_Views_Maestro',
			'method' => 'verHorarioPDF',
			'name' => 'verHorarioPorDepartamentoPDF',
		),
		array (
			'regex' => '#^(\d+)/carga/$#',
			'base' => $base,
			'model' => 'Calif_Views_Maestro',
			'method' => 'verCarga',
		),
		array (
			'regex' => '#^(\d+)/carga/constancia/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Maestro',
			'method' => 'constanciaCargaHoraria',
		),
		array (
			'regex' => '#^(\d+)/permisos/$#',
			'base' => $base,
			'model' => 'Calif_Views_Maestro',
			'method' => 'permisos',
		)
	)
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

$ctl[] = array (
	'regex' => '#^/departamentos/buscarerror/$#',
	'base' => $base,
	'model' => 'Calif_Views_Departamento',
	'method' => 'buscarErrorHoras',
);

/* Número de Puestos */
$ctl[] = array (
	'regex' => '#^/puesto/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Puestos',
	'method' => 'actualizarPuesto',
);

$ctl[] = array (
	'regex' => '#^/reportes/$#',
	'base' => $base,
	'model' => 'Calif_Views_Reportes',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/reporte/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^profesores/carga/$#',
			'base' => $base,
			'model' => 'Calif_Views_Reportes_Maestro',
			'method' => 'cargaHoraria',
		),
		array (
			'regex' => '#^profesores/carga/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Reportes_Maestro',
			'method' => 'cargaHorariaPorDepartamento',
		)
	)
);

/* usuarios*/
$ctl[] = array(
	'regex' => '#^/permisos/add/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Usuario',
	'method' => 'agregarPermiso',
);

$ctl[] = array(
	'regex' => '#^/permisos/addGrupo/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Usuario',
	'method' => 'agregarGrupo',
);

$ctl[] = array(
	'regex' => '#^/titulacion#',
	'base' => $base,
	'sub' => require Gatuf::config ('titulacion_views'),
);

$ctl[] = array(
	'regex' => '#^/test/$#',
	'base' => $base,
	'model' => 'Calif_Views_Test',
	'method' => 'test',
);
return $ctl;
