<?php

namespace weikit\services;

use Yii;
use weikit\models\Module;
use weikit\models\Account;
use weikit\models\ModuleBinding;
use weikit\core\service\BaseService;

class MenuService extends BaseService
{
    /**
     * 获取Web模块导航菜单
     *
     * @return array
     */
    public function getWebNavMenu()
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
     * 获取Web模块导航右菜单
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getWebRightNavMenu()
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

    /**
     * 获取菜单数据
     *
     * @param $mid
     *
     * @return array
     */
    public function getModuleMenu($moduleName)
    {
        return Yii::$app->cache->getOrSet('menu_module:' . $moduleName, function () use ($moduleName) {
            /* @var $service ModuleService */
            $service = Yii::createObject(ModuleService::class);
            $module = $service->findByName($moduleName);
            $entries = $module->entries;

            $customMenu = [];
            foreach ($entries as $entry) {
                if ($entry->entry === ModuleBinding::ENTRY_MENU) {
                    $customMenu[] = [
                        'label' => $entry->title,
                        'url'   => ['/web/site/entry', 'eid' => $entry->eid],
                    ];
                }
            }

            $menu = [
                'entry'  => [
                    'label' => $module->title,
                    'items' => [
                    ],
                ],
                'custom' => [
                    'label' => '自定义',
                    'items' => $customMenu,
                ],
            ];

            return $menu;

        });
    }

    /**
     * 获取微信账号分类菜单数据
     *
     * @return array
     */
    public function getWechatAccountCategoryMenu()
    {
        /* @var ModuleService $service */
        $service = Yii::createObject(ModuleService::class);
        $data = $service->search();
        return [
            'basic' => [
                'label' => '基本设置',
                'items' => [
                    ['label' => '自动回复', 'url' => [ '/wechat/simulator/index' ]]
                ],
            ],
            'fans' => [
                'label' => '粉丝管理',
                'items' => [
                    ['label' => '自动回复', 'url' => [ '/wechat/simulator/index' ]]
                ],
            ],
            'addon' => [
                'label' => '扩展模块',
                'items' => array_map(function($module) {
                    /** @var Module $module */
                    return ['label' => $module->title, 'url' => '#'];
                }, $data['dataProvider']->models),
            ],
            'settings' => [
                'label' => '参数配置',
                'items' => [
                    ['label' => '自动回复', 'url' => [ '/wechat/simulator/index' ]]
                ],
            ],
        ];
    }
}
