<?php

namespace weikit\core\service;

use Closure;
use yii\base\Model;
use yii\web\Request;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use weikit\core\exceptions\ModelNotFoundException;

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
     * @return array|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]|null
     * @throws InvalidConfigException
     * @throws ModelNotFoundException
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
            // 自定义查询
            'query' => null,
        ], $options);

        if ( ! is_subclass_of($options['modelClass'], ActiveRecord::class)) {
            throw new InvalidConfigException('The modelClass must be subclass of "Model" or "ActiveRecord"');
        }

        /* @var $class \yii\db\ActiveRecord */
        $class = $options['modelClass'];

        $query = $class::find();

        if ( !empty($condition)) {
            // find by primary key
            if (!ArrayHelper::isAssociative($condition)) {
                // query by primary key
                $primaryKey = $class::primaryKey();
                if (isset($primaryKey[0])) {
                    // if condition is scalar, search for a single primary key, if it is array, search for multiple primary key values
                    $condition = [$primaryKey[0] => is_array($condition) ? array_values($condition) : $condition];
                } else {
                    throw new InvalidConfigException('"' . get_called_class() . '" must have a primary key.');
                }
            }

            $query->andWhere($condition);
        }

        // query callback
        if ($options['query'] instanceof Closure) {
            call_user_func($options['query'], $query);
        }

        // findAll
        if ($options['all']) {
            return $query->all();
        }

        // findOne
        $model = $query->one();

        if ($options['exception'] && $model === null) {
            throw new ModelNotFoundException($class);
        }

        return $model;
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