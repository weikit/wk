<?php

namespace weikit\modules\web;

use Yii;
use yii\filters\AccessControl;
use weikit\services\AccountService;
use weikit\core\traits\ControllerMessageTrait;

class Controller extends \yii\web\Controller
{
    use ControllerMessageTrait;
    /**
     * @var null
     */
    public $menu = null;
    /**
     * @var bool 是否需要检查设置的账号
     */
    public $enableAccountManageCheck = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = [];

        if ($this->enableAccountManageCheck) {
            $behaviors['access'] = [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'         => true,
                        'roles'         => ['@'], // 登录才能操作后台
                        'matchCallback' => function () {
                            /** @var AccountService $service */
                            $service = Yii::createObject(AccountService::class);
                            if (!$service->managingUniacid) {
                                $this->flash('未设置管理平台, 请先选则需要管理的平台', 'error', ['/wechat/wechat']);

                                return false;
                            }

                            return true;
                        },
                    ],
                ],
            ];
        }

        return $behaviors;
    }
}