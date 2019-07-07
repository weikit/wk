<?php

namespace weikit\core\addon;

use Yii;
use weikit\services\ModuleService;
use yii\helpers\ArrayHelper;

// 兼容代码
require_once __DIR__ . '/compat.php';

trait ModuleTrait
{
    /**
     * 缓存模块KEY
     */
//    const CACHE_ADDON_MODULES = 'cache_addon_modules'; // TODO 用常量代替defaultModules获取cackeKey

    /**
     * 获取扩展模块列表
     *
     * @return array|mixed
     */
    protected function defaultModules()
    {
        // TODO 把所有扩展模块统一注册到Yii::$classMap中

        /* @var $service ModuleService */
        $service = Yii::createObject(ModuleService::class);

        $modules = [];
        foreach($this->resolveModuleNames() as $name) {

            $moduleClass = $name . 'Module';
            Yii::$classMap[$moduleClass] = $service->getVirtualPath($name, 'module.php');

            $modules[$name] = [
                'class' => $moduleClass
            ];
        }
        return $modules;
    }

    /**
     * @return string[]
     */
    protected function resolveModuleNames()
    {
        // TODO cache dependency
        return Yii::$app->cache->getOrSet('cache_addon_module_names', function() {
            /* @var $service ModuleService */
            $service = Yii::createObject(ModuleService::class);
            /** @var $modules \weikit\models\Module[] */
            $modules = $service->findAll([]);
            return ArrayHelper::getColumn($modules, ['name']);
        });
    }
}