<?php

namespace weikit\core\service;

use yii\base\Model;
use yii\web\Request;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use weikit\core\db\ActiveRecord;

abstract class BaseService extends BaseObject
{
    /**
     * @var string
     */
    public $modelClass;

    /**
     * @param array $condition
     * @param array $options
     *
     * @return ActiveRecord|null
     */
    public function findBy($condition, array $options = [])
    {
        return $this->powerFind($condition, array_merge($options, [
            'all' => false
        ]));
    }


    /**
     * @param array $condition
     * @param array $options
     *
     * @return ActiveRecord[]|null
     */
    public function findAllBy($condition, array $options = [])
    {
        return $this->powerFind($condition, array_merge($options, [
            'all' => true
        ]));
    }

    /**
     * 标准数据查找函数
     *
     * @param $condition
     * @param array $options
     *
     * @return mixed
     */
    protected function powerFind($condition, array $options = [])
    {
        $options = array_merge([
            // 未查找到数据是否抛出异常, 只支持查找单行(findOne)数据
            'exception' => true,
            // 数据查找model类
            'modelClass' => $this->modelClass,
            // 是否查询多行数据
            'all' => false,
        ], $options);

        if ($options['modelClass'] === null) {
            throw new InvalidConfigException('The modelClass property must be set');
        } elseif ( ! is_subclass_of($options['modelClass'], ActiveRecord::class)) {
            throw new InvalidConfigException('The modelClass must be subclass of "Model" or "ActiveRecord"');
        }

        $class = $options['modelClass'];
        if ($options['all']) {
            $method = 'findAll';
        } else {
            $method = $options['exception'] ? 'tryFindOne' : 'findOne';
        }

        return call_user_func_array([$class, $method], [$condition]);
    }

    /**
     * @param $model
     * @param Request|array $requestOrData
     *
     * @return bool
     */
    protected function isModelLoad(Model $model, $requestOrData)
    {
        return $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '');
    }
}