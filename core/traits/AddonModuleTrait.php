<?php

namespace weikit\core\traits;

use Yii;
use weikit\services\ModuleService;

trait AddonModuleTrait
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
        // TODO cache dependency
        return Yii::$app->cache->getOrSet('cache_addon_modules', function() {
            /* @var $service ModuleService */
            $service = Yii::createObject(ModuleService::class);
            $modules = [];
            /* @var $model \weikit\models\Module */
            foreach ($service->findAllBy([]) as $model) {
                $modules[$model->name] = [
                    'class' => 'weikit\addon\Module',
                ];
            }
            return array_merge($modules, $this->coreModules());
        });
    }

    protected function coreModules()
    {
        return [
        ];
    }
}