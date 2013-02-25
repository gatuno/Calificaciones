<?php

function Calif_Migrations_Install_setup($params=null) {
    $models = array('Calif_Carrera',
                    );
    $db = Pluf::db();
    $schema = new Pluf_DB_Schema($db);
    foreach ($models as $model) {
        $schema->model = new $model();
        $schema->createTables();
    }
}

function Calif_Migrations_Install_teardown($params=null)
{
    $models = array('Calif_Carrera',
                    );
    $db = Pluf::db();
    $schema = new Pluf_DB_Schema($db);
    foreach ($models as $model) {
        $schema->model = new $model();
        $schema->dropTables();
    }
}
