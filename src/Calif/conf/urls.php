<?php
$base = Gatuf::config('calif_base');
$ctl = array ();

$ctl[] = array(
	'regex' => '#^/carrera/$#',
	'base' => $base,
	'priority' => 4,
	'model' => 'Calif_Views_Carrera',
	'method' => 'index',
);

$ctl[] = array(
	'regex' => '#^/carrera/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Carrera',
	'method' => 'agregarCarrera',
);

$ctl[] = array(
	'regex' => '#^/carrera/(\w+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Carrera',
	'method' => 'verCarrera',
);

return $ctl;
