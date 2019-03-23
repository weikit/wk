<?php

namespace weikit\modules\web;

use Yii;
use weikit\services\ModuleService;

class Module extends \yii\base\Module
{
    /**
     * 模块数据缓存
     */
    const CACHE_ADDON_MODULES = 'cache_addon_modules';
    /**
     * @var string
     */
    public $controllerNamespace = 'weikit\modules\web\controllers';

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, $config = [])
    {
        parent::__construct($id, $parent, array_merge([
            'modules' => $this->defaultModules()
        ], $config));
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        \We8::initWeb();
        require_once WEIKIT_PATH . '/core/addon/compat.php';
    }

    /**
     * 获取扩展模块列表
     *
     * @return array|mixed
     */
    protected function defaultModules()
    {
        $cache = Yii::$app->cache;
        if (($modules = $cache->get(self::CACHE_ADDON_MODULES)) === false) {
            /* @var $service ModuleService */
            $service = Yii::createObject(ModuleService::class);
            $modules = [];
            /* @var $model \weikit\models\Module */
            foreach ($service->findAllBy([]) as $model) {
                $modules[$model->name] = [
                    'class' => 'weikit\core\addon\Module',
                ];
            }
            // TODO cache dependency
            $cache->set(self::CACHE_ADDON_MODULES, $modules);
        }
        return $modules;
    }
}