<?php

namespace weikit\core\model;

use yii\web\Request;
use weikit\core\exceptions\ModelValidationException;

trait ModelLoadTrait
{
    /**
     * @param $model
     * @param Request|array $requestOrData
     *
     * @return bool
     */
    public function loadFrom($requestOrData)
    {
        return $requestOrData instanceof Request ?
            $this->load($requestOrData->post()) :
            $this->load($requestOrData, '');
    }
}