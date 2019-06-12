<?php

namespace weikit\core\addon;

use Yii;
use ArrayAccess;
use weikit\models\ModuleBinding;
use weikit\services\ModuleService;
use weikit\services\AccountService;
use weikit\models\Module as ModuleModel;

// 兼容代码
require_once __DIR__ . '/compat.php';

/**
 * Class Module
 * @package weikit\core\addon
 *
 * @property int uniacid
 * @property ModuleModel $model
 * @property array config
 */
class Module extends \yii\base\Module implements ArrayAccess
{
    /**
     * 扩展模块菜单缓存键
     */
    const CACHE_ADDON_MODULE_MENU_PREFIX = 'menu_addon_module';
    /**
     * @var int
     */
    private $_uniacid;
    /**
     * @var ModuleModel
     */
    private $_model;
    /**
     * @var array
     */
    private $_config;
    /**
     * @var ModuleService
     */
    public $service;
    /**
     * @inheritdoc
     */
    public function  __construct($id, $parent = null, ModuleService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $parent, array_merge([
            'controllerMap' => $this->defaultControllerMap($id, $parent),
            'controllerNamespace' => 'weikit\core\addon\controllers\\' . $parent->id, // TODO 切换模块命名空间
        ]), $config);
    }

    public function init()
    {
        if ( ! defined('MODULE_ROOT')) {
            define('MODULE_ROOT', $this->service->getRealPath($this->id));
        }
        if ( ! defined('MODULE_URL')) {
            define('MODULE_URL', $this->service->getUrl($this->id) . '/');
        }
    }

    /**
     * @param string $name
     * @param Module $module
     *
     * @return array
     */
    protected function defaultControllerMap($name)
    {
        // TODO 扩展模块的功能(在安装时)放入Yii::$classMap(可优化性能)

        $entryClass = $name . 'ModuleSite';
        $moduleClass = $name . 'Module';

        Yii::$classMap[$entryClass] = $this->service->getVirtualPath($name, 'site.php');
        Yii::$classMap[$moduleClass] = $this->service->getVirtualPath($name, 'module.php');

        return [
            'entry' => $entryClass,
            'module' => $moduleClass,
        ];
    }

    /**
     * @return int
     */
    public function getUniacid()
    {
        if ($this->_uniacid === null) {
            /** @var $accountService AccountService */
            $accountService = Yii::createObject(AccountService::class);
            $this->setUniacid($accountService->managingUniacid);
        }
        return $this->_uniacid;
    }

    /**
     * @param int $uniacid
     */
    public function setUniacid($uniacid)
    {
        $this->_uniacid = $uniacid;
    }

    /**
     * @return ModuleModel
     */
    public function getModel(): ModuleModel
    {
        if ($this->_model === null) {
            $model = $this->service->findByName($this->id, [
                'query' => function($query) {
                    /* @var $query \yii\db\ActiveQuery */
                    $query->cache(); // TODO cache dependency
                }
            ]);
            $this->setModel($model);
        }
        return $this->_model;
    }

    /**
     * @param ModuleModel $model
     */
    public function setModel(ModuleModel $model)
    {
        $this->_model = $model;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $config = [];
            if ($uniacid = $this->uniacid) {
                $settings = $this->service->findAccountSettings($this->id, $uniacid);
                if ($settings) {
                    $config = $settings->settings;
                }
            }
            $this->setConfig($config);
        }
        return $this->_config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig(array $config)
    {
        $this->_config = $config;
    }

    /**
     * 默认和web/app模块共享模板路径
     *
     * @inheritdoc
     */
    public function getViewPath()
    {
        return $this->module->getViewPath();
    }

    /**
     * @param string $name
     *
     * @return mixed|object|null
     * @throws \yii\base\InvalidConfigException
     */
    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        }

        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            // read property, e.g. getName()
            return $this->$getter();
        }

        // TODO add behaviors support?
        return $this->model->$name;
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $item)
    {
        $this->$offset = $item;
    }

    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }
}