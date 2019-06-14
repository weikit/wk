<?php

namespace weikit\modules\app;

use weikit\core\addon\ModuleTrait;

class Module extends \yii\base\Module
{
    use ModuleTrait;
    /**
     * @var string
     */
    public $controllerNamespace = 'weikit\modules\app\controllers';
    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, $config = [])
    {
        parent::__construct($id, $parent, array_merge([
            'modules' => $this->defaultModules()
        ], $config));
    }
}