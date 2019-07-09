<?php

namespace weikit\core\addon;

use Yii;
use yii\base\BaseObject;
use weikit\services\ModuleService;

/**
 * 扩展模块相关缓存构建器
 *
 * @package weikit\core\addon
 */
class ModuleBuilder extends BaseObject
{
    /**
     * @var ModuleService
     */
    protected $service;

    public function __construct(ModuleService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($config);
    }

    public function getModules()
    {   // TODO 存到私有变量?
        return $this->service->findAll([], [
            'cache' => true,
            'cacheDependency' => [ModuleService::CACHE_TAG_ADDON_MODULES]
        ]);
    }

    /**
     * @return array
     */
    public function getClasses()
    {

        $modules = $this->getModules();

        $classes = [];

        foreach ($modules as $module) {
            $name = strtolower($module->name);

            $className = $name . 'Module';

            $classes[$className] = $this->service->getRealPath($module->name, 'module.php');

            $className = $name . 'ModuleSite';
            $classes[$className] = $this->service->getRealPath($module->name, 'site.php');

            $className = $name . 'ModuleProcessor';
            $classes[$className] = $this->service->getRealPath($module->name, 'processor.php');

            $className = $name . 'ModuleReceiver';
            $classes[$className] = $this->service->getRealPath($module->name, 'receiver.php');
        }

        return $classes;
    }
}