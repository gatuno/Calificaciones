<?php

require dirname(__FILE__).'/../src/Calif/conf/path.php';

# Cargar Pluf
require 'Pluf.php';

# Inicializar las configuraciones
Pluf::start(dirname(__FILE__).'/../src/Calif/conf/calif.php');

Pluf_Dispatcher::loadControllers(Pluf::f('calif_views'));
Pluf_Dispatcher::dispatch(Pluf_HTTP_URL::getAction());

