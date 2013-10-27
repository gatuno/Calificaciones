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
/* Conexión de señales aquí */

return $m;
