<?php

namespace weikit\services;

use Yii;
use yii\web\Request;
use weikit\models\AccountWechat;
use weikit\core\service\Service;
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
            $model->getDb()->transaction(function() use ($model) {
                /* @var $accountService AccountService */
                $accountService = Yii::createObject(AccountService::class);

                $uniAccount = $accountService->addUniAccount([
                    'name' => $model->name,
                ]);
                if (!$uniAccount->isNewRecord) {
                    // TODO throw error if save fail
                }

                $account = $accountService->add([
                    'uniacid' => $uniAccount->uniacid,
                ]);
                if (!$account->isNewRecord) {
                    // TODO throw error if save fail
                }

                $model->uniacid = $account->uniacid;
                $model->acid = $account->acid;
                $model->save();
            });
        }

        return $model;
    }

    /**
     * @param $id
     * @param $requestOrData
     *
     * @return AccountWechat
     * @throws \weikit\core\exceptions\ModelNotFoundException
     * @throws \weikit\core\exceptions\ModelValidationException
     */
    public function editById($id, $requestOrData)
    {
        $model = AccountWechat::tryFindOne($id);
        if (
            $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '')
        ) {
            $model->trySave();
        }
        return $model;
    }

    /**
     * @param $id
     *
     * @return false|int
     * @throws \weikit\core\exceptions\ModelNotFoundException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteById($id)
    {
        $model = AccountWechat::tryFindOne($id);

        return $model->delete();
    }
}