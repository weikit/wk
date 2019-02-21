<?php

namespace weikit\services;

use yii\web\Request;
use weikit\models\Account;
use weikit\models\UniAccount;
use weikit\core\service\Service;

class AccountService extends Service
{

    public function add($requestOrData)
    {
        $model = new Account();

        if (
            $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '')
        ) {
            $model->trySave();
        }

        return $model;
    }

    public function addUniAccount($requestOrData)
    {
        $model = new UniAccount();

        if (
            $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '')
        ) {
            $model->trySave();
        }

        return $model;
    }

}