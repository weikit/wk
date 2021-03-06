<?php

$config = [
    'id' => 'weikit',
    'name' => 'Weikit',
    'basePath' => WEIKIT_PLUGIN_PATH,
    'bootstrap' => [
        'weikit\core\Bootstrap',
        'log'
    ],
    'language' => 'zh-CN',
    'aliases' => [
        '@weikit' => WEIKIT_PATH,
        '@wp' => ABSPATH,
        '@wp_url' => site_url(),
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
        'db' => require __DIR__ . '/db.php',
        'user' => [
            'class' => 'weikit\core\User',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [ 'class' => 'weikit\core\rules\WeikitRule']
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
            'defaultExtension' => 'html',
            'renderers' => [
                'php' => [
                    'class' => 'weikit\core\view\PhpRenderer',
                ],
                'html' => [
                    'class' => 'weikit\core\view\HtmlRenderer',
                ],
//                'html' => [
//                    'class' => 'weikit\core\view\LatteRenderer',
//                ],
            ],
        ],
        'config' => [
            'class' => 'weikit\core\config\Config'
        ],
        'addon' => [
            'class' => 'weikit\addon\ModuleManager'
        ]
    ],
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
        'generators' => [
            'addon' => [
                'class' => 'weikit\generators\addon\Generator'
            ]
        ]
    ];
}

return $config;
