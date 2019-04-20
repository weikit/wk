<?php

namespace weikit\modules\web;

use Yii;
use weikit\models\Account;
use weikit\services\AccountService;
use weikit\core\traits\AddonModuleTrait;

class Module extends \yii\base\Module
{
    use AddonModuleTrait;
    /**
     * @var string
     */
    public $controllerNamespace = 'weikit\modules\web\controllers';
    /**
     * @var string
     */
    public $layout = 'main';
    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, $config = [])
    {
        parent::__construct($id, $parent, array_merge([
            'modules' => $this->defaultModules()
        ], $config));
    }

    /**
     * @return array
     */
    public function getNavMenu()
    {
        return [
            'wechat' => [
                'label' => '微信公众号',
                'url' => ['/web/account/wechat/index'],
            ],
            'wxapp' => [
                'label' => '微信小程序',
                'url' => ['/web/account/wxapp'],
            ],
            'aliapp' => [
                'label' => '支付宝小程序',
                'url' => ['/web/account/aliapp'],
                'items' => [
                    'aliapp' => [
                        'label' => '支付宝小程序1',
                        'url' => ['/web/account/aliapp'],
                    ],
                ]
            ],
        ];
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getRightNavMenu()
    {
        $menu = [
            'system' => [
                'label' => '系统',
                'url' => '#',
            ],
            'gii' => [
                'label' => 'Gii',
                'url' => ['/gii'],
            ],
            'emulator' => [
                'label' => '微信模拟器',
                'url' => ['/web/emulator/wechat'],
            ],
        ];
        /* @var AccountService $service */
        $service = Yii::createObject(AccountService::class);
        if ($service->managingUniacid) {
            $account = $service->managing();

            if ($account->type == Account::TYPE_WECHAT) {
                $menu['emulator'] = [
                    'label' => '微信模拟器',
                    'url' => ['/web/emulator/wechat'],
                ];
            }

            $menu['account'] = [
                'label' => '管理账号:' . $account->uniAccount->name,
                'url' => '#'
            ];
        }
        return $menu;
    }

    public function getSideMenu()
    {
        return [];
    }
}