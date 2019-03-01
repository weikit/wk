<?php

namespace weikit\modules\web\controllers\account;

use weikit\models\Account;
use weikit\services\AccountService;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @var AccountService
     */
    protected $service;

    /**
     * @inheritdoc
     */
    public function __construct($id, $module, AccountService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function actionSwitch($uniacid)
    {
        $account = $this->service->manage($uniacid);

        switch ($account->type) {
            case $account::TYPE_WECHAT:
                return $this->redirect(['account/wechat/manage']);
                break;
            default:
                throw new NotFoundHttpException('Account type is incorrect');
        }
    }

    public function switchTo(Account $model)
    {
        
    }
}