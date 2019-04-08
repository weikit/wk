<?php

namespace weikit\core\traits;

use Yii;
use weikit\services\ModuleService;

trait AddonModuleTrait
{
    /**
     * 获取扩展模块列表
     *
     * @return array|mixed
     */
    protected function defaultModules()
    {
        $cache = Yii::$app->cache;
        // TODO cacheKey常量
        if (($modules = $cache->get('cache_addon_modules')) === false) {
            /* @var $service ModuleService */
            $service = Yii::createObject(ModuleService::class);
            $modules = [];
            /* @var $model \weikit\models\Module */
            foreach ($service->findAllBy([]) as $model) {
                $modules[$model->name] = [
                    'class' => 'weikit\addon\Module',
                ];
            }
            // TODO cache dependency
            $cache->set('cache_addon_modules', $modules);
        }
        return $modules;
    }
}