<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'weikit',
    'basePath' => WEIKIT_PATH,
    'bootstrap' => ['log'],
    'aliases' => [
        '@weikit_path' => WEIKIT_PATH,
        '@weikit' => home_url(str_replace(ABSPATH, '', WEIKIT_PATH)),
        '@wp_path' => ABSPATH,
        '@wp' => home_url(),
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'RiUZnGwwB9rgS-YWBsFpF3myviBIwkAY',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'user' => [
            'class' => 'weikit\core\User',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
//            'showScriptName' => false,
            'rules' => [
                [ 'class' => 'weikit\core\rules\WeikitRule' ]
            ],
        ],
        'assetManager' => [
            'basePath' => '@weikit_path/web/assets',
            'baseUrl' => '@weikit/web/assets',
            'bundles' => [
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'jsOptions' => [
                        'position' => yii\web\View::POS_HEAD
                    ]
                ],
            ],
        ],
        'view' => [
            'class' => 'weikit\core\View',
//            'defaultExtension' => 'html',
//            'renderers' => [
//                'html' => [
//                    'class' => 'weikit\core\HtmlViewRenderer',
//                ],
//                // ...
//            ],
        ],
    ],
    'params' => $params,
    'modules' => [
        'app' => [
            'class' => 'weikit\modules\app\Module',
        ],
        'web' => [
            'class' => 'weikit\modules\web\Module',
        ],
    ]
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
        'panels' => [
            'config' => ['class' => 'yii\debug\panels\ConfigPanel'],
            'request' => ['class' => 'yii\debug\panels\RequestPanel'],
            'log' => ['class' => 'yii\debug\panels\LogPanel'],
            'profiling' => ['class' => 'yii\debug\panels\ProfilingPanel'],
            'db' => ['class' => 'yii\debug\panels\DbPanel'],
            'event' => ['class' => 'yii\debug\panels\EventPanel'],
            'assets' => ['class' => 'yii\debug\panels\AssetPanel'],
            'mail' => ['class' => 'yii\debug\panels\MailPanel'],
            'timeline' => ['class' => 'yii\debug\panels\TimelinePanel'],
            'user' => null, // TODO wp user support
            'router' => ['class' => 'yii\debug\panels\RouterPanel'],
        ]
    ];
}

return $config;
