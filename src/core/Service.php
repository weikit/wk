<?php

namespace weikit\core;

use Closure;
use yii\base\Model;
use yii\web\Request;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\caching\TagDependency;
use yii\base\InvalidConfigException;
use weikit\core\exceptions\ModelNotFoundException;

class Service extends BaseObject
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
    public function findOne($condition = [], array $options = [])
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
    public function findAll($condition = [], array $options = [])
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
            // 联表冲突时的别名设置
            'alias' => null,
            // int类型 缓存查询记录, 开启并缓存的时间
            'cache' => false,
            // 缓存依赖
            'cacheDependency' => null,
        ], $options);

        if ( ! is_subclass_of($options['modelClass'], ActiveRecord::class)) {
            throw new InvalidConfigException('The modelClass must be subclass of "Model" or "ActiveRecord"');
        }

        /* @var $class \weikit\core\db\ActiveRecord */
        $class = $options['modelClass'];

        $query = $class::find();

        if ($options['alias'] !== null) {
            $query->alias($options['alias']);
        }

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

        // query cache
        if ($options['cache'] !== false) {
            $duration = is_numeric($options['cache']) ? $options['cache'] : 0;
            $dependency = is_array($options['cacheDependency']) || is_string($options['cacheDependency']) ? new TagDependency([
                'tags' => $options['cacheDependency'],
            ]) : $options['cacheDependency'];
            $query->cache($duration, $dependency);
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