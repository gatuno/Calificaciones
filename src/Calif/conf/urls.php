<?php
$base = Gatuf::config('calif_base');
$ctl = array ();

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

return $ctl;
