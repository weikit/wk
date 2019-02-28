<?php

namespace weikit\modules\web;

use weikit\core\We8;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'weikit\modules\web\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        We8::initWeb();
    }
}