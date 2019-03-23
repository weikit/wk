<?php

namespace weikit\core\addon;

use weikit\services\ModuleService;
use Yii;
use ArrayAccess;
use weikit\models\Module as ModuleModel;

/**
 * Class Module
 * @package weikit\core\addon
 * @property ModuleModel $model
 */
class Module extends \yii\base\Module implements ArrayAccess
{
    /**
     * @var ModuleModel
     */
    private $_model;

    /**
     * @var string
     */
    public $controllerNamespace = 'weikit\core\addon\web\controllers';

    /**
     * @inheritdoc
     */
    public function  __construct($id, $parent = null, $config = [])
    {
        parent::__construct($id, $parent, array_merge([
            'controllerMap' => $this->defaultControllerMap($id)
        ]));
    }

    /**
     * @param string $name
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function defaultControllerMap($name)
    {
        // TODO 增加扩展模块的(安装时)扩展功能放入Yii::classes中(可优化性能)
        require_once Yii::getAlias('@wp/addons/' . $name . '/site.php');
        return [
            'entry' => $name . 'ModuleSite',
        ];
    }

    /**
     * @return ModuleModel
     */
    public function getModel(): ModuleModel
    {
        if ($this->_model === null) {
            /* @var $service ModuleService */
            $service = Yii::createObject(ModuleService::class);
            $model = $service->findByName($this->id, [
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
}