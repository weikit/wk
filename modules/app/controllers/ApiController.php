<?php

namespace weikit\modules\app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use weikit\models\Account;
use weikit\services\AccountService;
use weikit\addon\wechat\ApiHandler;

class ApiController extends Controller
{
    /**
     * API所有请求关闭CSRF验证
     * @var bool
     */
    public $enableCsrfValidation = false;

    public function actionIndex($hash = null, $id = null)
    {
        $account = $this->findAccountDataByHashOrAcid($hash ?? $id);

        return $this->handleMessage($account);
    }

    /**
     * @param $hashOrAcid
     *
     * @return array|\weikit\models\Account|null
     */
    protected function findAccountDataByHashOrAcid($hashOrAcid)
    {
        /* @var $service AccountService */
        $service = Yii::createObject(AccountService::class);
        $options = [
            'query' => function($query) {
                $query->with('uniAccount')->cache();
            }
        ];
        if (is_numeric($hashOrAcid)) {
            return $service->findByAcid($hashOrAcid, $options);
        }

        return $service->findByHash($hashOrAcid, $options);
    }

    protected function handleMessage(Account $account)
    {
        if ($account->type === Account::TYPE_WECHAT) {
            $handler = Yii::createObject(ApiHandler::class, [$account]);
        } else {
            throw new BadRequestHttpException('Only support wechat message.');
        }
    }
}