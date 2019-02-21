<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'weikit',
    'name' => 'Weikit',
    'basePath' => WEIKIT_PATH,
    'bootstrap' => ['log'],
    'language' => 'zh-CN',
    'aliases' => [
        '@weikit' => WEIKIT_PATH,
        '@weikit_url' => home_url(str_replace(ABSPATH, '', WEIKIT_PATH)),
        '@wp' => ABSPATH,
        '@wp_url' => home_url(),
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
            'bundles' => [
                'yii\gii\GiiAsset' => [
                    'depends' => [
                        'yii\web\YiiAsset',
                        'yii\bootstrap\BootstrapAsset',
                        'yii\bootstrap\BootstrapPluginAsset',
                        'yii\gii\TypeAheadAsset',
                        'weikit\assets\IframeResizerContentAsset'
                    ]
                ]
            ]
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
            'user' => null, // TODO wp user support
        ]
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
