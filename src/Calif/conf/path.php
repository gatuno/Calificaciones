<?php
define('PLUF_PATH', '/home/www/pluf/src');
define('CALIF_PATH', dirname(__FILE__).'/../..');

set_include_path(get_include_path()
                 .PATH_SEPARATOR.PLUF_PATH
                 .PATH_SEPARATOR.CALIF_PATH
                 );
