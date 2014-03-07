<?php

$m = array ();

$m['Calif_Alumno'] = array ('relate_to' => array ('Calif_Carrera'),
                            'relate_to_many' => array ('Calif_Seccion'));
$m['Calif_Evaluacion'] = array ('relate_to' => array ('Calif_GrupoEvaluacion'));
$m['Calif_Horario'] = array ('relate_to' => array ('Calif_Seccion', 'Calif_Salon'));
$m['Calif_Materia'] = array ('relate_to' => array ('Calif_Departamento'),
                             'relate_to_many' => array ('Calif_Carrera'));
$m['Calif_Porcentaje'] = array ('relate_to' => array ('Calif_Materia', 'Calif_Evaluacion'));
$m['Calif_Salon'] = array ('relate_to' => array ('Calif_Edificio'));
$m['Calif_Seccion'] = array ('relate_to' => array ('Calif_Materia', 'Calif_Maestro', 'Calif_Carrera'));
$m['Calif_Calificacion'] = array ('relate_to' => array ('Calif_Seccion', 'Calif_Alumno', 'Calif_Evaluacion'));
$m['Calif_Maestro'] = array ('relate_to' => array ('Calif_Nombramiento'));
$m['Calif_NumeroPuesto'] = array ('relate_to' => array ('Calif_Seccion'));
$m['Calif_Carrera'] = array ('relate_to' => array ('Calif_Division'));

/* Conexión de señales aquí */
Gatuf_Signal::connect('Calif_Seccion::created', array ('Calif_NumeroPuesto', 'nrc_creado'));
Gatuf_Signal::connect('Calif_Seccion::maestroUpdated', array ('Calif_NumeroPuesto', 'maestro_cambiado'));
Gatuf_Signal::connect('Calif_Seccion::suplenteCreated', array ('Calif_NumeroPuesto', 'suplente_creado'));
Gatuf_Signal::connect('Calif_Seccion::suplenteUpdated', array ('Calif_NumeroPuesto', 'suplente_cambiado'));
Gatuf_Signal::connect('Calif_Seccion::suplenteDelete', array ('Calif_NumeroPuesto', 'suplente_eliminado'));
Gatuf_Signal::connect('Calif_Materia::horasUpdated', array ('Calif_NumeroPuesto', 'materia_horas_actualizado'));
return $m;
