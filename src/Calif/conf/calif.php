<?php
$cfg = array();

$cfg['debug'] = true;

$cfg['admins'] = array(
	array('Admin', 'gatuno_123@esdebian.org'),
);

# Llave de instalación,
# Debe ser única para esta instalación y lo suficientemente larga (40 caracteres)
# Puedes generar una llave con:
#	$ dd if=/dev/urandom bs=1 count=64 2>/dev/null | base64 -w 0
$cfg['secret_key'] = 'qYM6qm5X40xgCsHUgeAqFE48/xvC++OyVH+pYVgF4COBkZ6l6iWOz0Wu7kB';

# ---------------------------------------------------------------------------- #
#                                   Rutas                                      #
# ---------------------------------------------------------------------------- #

# Carpeta temporal donde la aplicación puede crear plantillas complicadas,
# datos en caché y otros recursos temporales.
# Debe ser escribible por el servidor web.
$cfg['tmp_folder'] = '/tmp';

# Ruta a la carpeta PEAR
$cfg['pear_path'] = '/usr/share/php';

# ---------------------------------------------------------------------------- #
#                                URL section                                   #
# ---------------------------------------------------------------------------- #

# Ejemplos:
# Tienes:
#   http://www.mydomain.com/myfolder/index.php
# Pon:
#   $cfg['calif_base'] = '/myfolder/index.php';
#   $cfg['url_base'] = 'http://www.mydomain.com';
#
# Tienes activado mod_rewrite:
#   http://www.mydomain.com/
# Pon:
#   $cfg['calif_base'] = '';
#   $cfg['url_base'] = 'http://www.mydomain.com';
#
$cfg['calif_base'] = '/gatuno';
$cfg['url_base'] = 'http://alanturing.cucei.udg.mx';

# ---------------------------------------------------------------------------- #
#                      Sección de internacionalización                         #
# ---------------------------------------------------------------------------- #

# La zona horaria
# La lista de zonas horarios puede ser encontrado aqui
# <http://www.php.net/manual/en/timezones.php>
$cfg['time_zone'] = 'America/Mexico_City';

# ---------------------------------------------------------------------------- #
#                             Database section                                 #
# ---------------------------------------------------------------------------- #
#
#

# El nombre de la base de datos para MySQL y PostgreSQL, y la ruta absoluta
# al archivo de la base de datos si estás usando SQLite.
$cfg['db_database'] = 'calificaciones';

# El servidor a conectarse
$cfg['db_server'] = 'localhost';

# Información del usuario.
$cfg['db_login'] = 'calificaciones';
$cfg['db_password'] = 'WzScQqqf3AjtLPdL';

# Un prefijo para todas tus tabla; esto puede ser útil si piensas correr
# multiples instalaciones en la misma base de datos.
$cfg['db_table_prefix'] = '';

$cfg['calif_views'] = dirname(__FILE__).'/urls.php';

# Ruta de los templates
$cfg['template_folders'] = array(
   dirname(__FILE__).'/../templates',
);


return $cfg;
