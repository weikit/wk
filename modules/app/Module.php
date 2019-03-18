<?php

namespace weikit\modules\app;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'weikit\modules\app\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        \We8::initApp();
        require_once WEIKIT_PATH . '/core/addon/compat.php';
    }
}