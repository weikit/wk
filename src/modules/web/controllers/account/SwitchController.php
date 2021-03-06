<?php

namespace weikit\modules\web\controllers\account;

use weikit\modules\web\Controller;
use weikit\services\AccountService;

class SwitchController extends Controller
{
    /**
     * @var AccountService
     */
    protected $service;
    /**
     * @var bool
     */
    public $enableAccountManageCheck = false;

    /**
     * @inheritdoc
     */
    public function __construct($id, $module, AccountService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex($uniacid)
    {
        $account = $this->service->manage($uniacid);

        switch ($account->type) {
            case $account::TYPE_WECHAT:
                return $this->redirect(['account/wechat/home']);
                break;
            default:
                throw new NotFoundHttpException('Account type is incorrect');
        }
    }
}