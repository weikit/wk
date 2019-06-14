<?php

namespace weikit\modules\web;

use Yii;
use weikit\models\Rule;
use weikit\core\traits\AddonModuleTrait;
use weikit\core\addon\Module as AddonModule;
use weikit\exceptions\AddonModuleNotFoundException;

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
            'modules' => $this->defaultModules()
        ], $config));
    }

    public function runModuleReplyForm(Rule $model)
    {
        $name = $model->module;
        /* @var $module AddonModule */
        $module = $this->getModule($name);

        if (empty($module)) {
            throw new AddonModuleNotFoundException($name);
        }

        $request = Yii::$app->request;
        if ($request->isPost) {

        }

        return $module->fieldsFormDisplay($module->rid);
    }
}