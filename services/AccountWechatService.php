<?php

namespace weikit\services;

use yii\web\Request;
use weikit\core\Service;
use weikit\models\AccountWechat;
use weikit\models\AccountWechatSearch;

class AccountWechatService extends Service
{
    /**
     * @param array $query
     *
     * @return array
     */
    public function search(array $query)
    {
        $searchModel = new AccountWechatSearch();
        $dataProvider = $searchModel->search($query);
        return compact('searchModel', 'dataProvider');
    }

    /**
     * @param $id
     *
     * @return AccountWechat|null
     */
    public function findById($id)
    {
        return AccountWechat::findOne($id);
    }

    /**
     * @param Request|array $request
     *
     * @return AccountWechat
     */
    public function add($requestOrData)
    {
        $model = new AccountWechat();

        if (
            $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '')
        ) {
            $model->save();
        }

        return $model;
    }

    /**
     * @param $id
     * @param Request|array $request
     *
     * @return AccountWechat|null
     */
    public function editById($id, $requestOrData)
    {
        $model = $this->findById($id);
        if (
            $model !== null &&
            (
                $requestOrData instanceof Request ?
                $model->load($requestOrData->post()) :
                $model->load($requestOrData, '')
            )
        ) {
            $model->save();
        }
        return $model;
    }

    /**
     * @param $id
     *
     * @return false|int|null
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteById($id)
    {
        $model = $this->findById($id);
        if ($model !== null) {
            return $model->delete();
        }
        return $model;
    }
}