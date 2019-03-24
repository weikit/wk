<?php

// TODO 代码块合理放置位置
\yii\base\Event::on(\yii\db\Connection::class, \yii\db\Connection::EVENT_AFTER_OPEN, function ($event) {
    /* @var $connection \yii\db\Connection */
    $connection = $event->sender;
    if ($connection->driverName === 'mysql') {
        // 关闭Mysql sql_mode模式检查
        $connection->pdo->exec("SET SESSION sql_mode='';");
    }
});

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
