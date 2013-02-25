<?php
$base = Pluf::f('calif_base');
$ctl = array ();

$ctl[] = array(
	'regex' => '#^/carrera/$#',
	'base' => $base,
	'priority' => 4,
	'model' => 'Calif_Views_Carrera',
	'method' => 'index',
);

$ctl[] = array(
	'regex' => '#^/carrera/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Carrera',
	'method' => 'verCarrera',
);

return $ctl;
