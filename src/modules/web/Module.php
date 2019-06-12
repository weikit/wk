<?php

namespace weikit\modules\web;

use weikit\core\traits\AddonModuleTrait;

class Module extends \yii\base\Module
{
    use AddonModuleTrait;
    /**
     * @var string
     */
    public $controllerNamespace = 'weikit\modules\web\controllers';
    /**
     * @var string
     */
    public $layout = 'main';
    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, $config = [])
    {
        parent::__construct($id, $parent, array_merge([
            'modules' => Yii::$app->addon->getModules(),
        ], $config));
    }
}