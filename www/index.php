<?php

require dirname(__FILE__).'/../src/Calif/conf/path.php';

# Cargar Gatuf
require 'Gatuf.php';

# Inicializar las configuraciones
Gatuf::start(dirname(__FILE__).'/../src/Calif/conf/calif.php');

Gatuf_Despachador::loadControllers(Gatuf::config('calif_views'));

Gatuf_Despachador::despachar(Gatuf_HTTP_URL::getAction());

