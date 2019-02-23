<?php

namespace weikit\core\service;

use yii\base\Model;
use yii\web\Request;

abstract class BaseService
{
    /**
     * @param $model
     * @param Request|array $requestOrData
     * @return bool
     */
    protected function isModelLoad(Model $model, $requestOrData)
    {
        return $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '');
    }
}