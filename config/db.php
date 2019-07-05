<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
    'username' => DB_USER,
    'password' => DB_PASSWORD,
    'charset' => DB_CHARSET,
    'tablePrefix' => 'ims_',

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    //'schemaCache' => 'cache',
];
