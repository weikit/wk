<?php

namespace weikit\core\addon;

use Yii;
use yii\base\Component;
use weikit\services\ModuleService;

class ModuleManager extends Component
{
    protected $modules;
    protected $coreModules;

    /**
     * 获取扩展模块列表
     *
     * @return array|mixed
     */
    public function modules()
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

    public function get($name)
    {

    }
}