<?php

namespace weikit\modules\web\controllers;

use yii\helpers\Url;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use weikit\models\Account;

class EmulatorController extends Controller
{
    public function actionWechat()
    {
        // TODO 放入service中
        $accounts = Account::find()
            ->where(['type' => Account::TYPE_WECHAT])
            ->innerJoinWith(['wechatAccount', 'uniAccount'])
            ->all();

        return $this->render('wechat', [
            'accounts' => ArrayHelper::map($accounts, 'acid', function($account) {
                return [
                    'name' => $account->uniAccount->name,
                    'hash' => $account->hash,
                    'token' => $account->wechatAccount->token,
                    'apiUrl' => Url::to(['/app/api', 'hash' => $account->hash]),
//                    'apiUrl' => Url::to('api.php?' . http_build_query(['hash' => $account->hash]))
                ];
            }),
        ]);
    }
}