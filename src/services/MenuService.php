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
     * 扩展模块菜单缓存键
     */
    const CACHE_ADDON_MODULE_MENU_PREFIX = 'menu_addon_module';

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
                'items' => [
                    ['label' => '模块管理', 'url' => ['/web/module/index']],
                    '<li class="divider"></li>',
                    ['label' => '更新缓存', 'url' => ['/web/system/index']]
                ]
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
                $menu['account'] = [
                    'label' => '管理账号:' . $account->uniAccount->name,
                    'url' => ['/web/account/wechat/home']
                ];
            }
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
    public function getAddonModuleMenu($moduleId)
    {
        return Yii::$app->cache->getOrSet(self::CACHE_ADDON_MODULE_MENU_PREFIX . ':' . $moduleId, function() use($moduleId) {
            /* @var $service ModuleService */
            $service = Yii::createObject(ModuleService::class);
            $module = $service->findByName($moduleId);
            $entries = $module->entries;

            $customMenu = [];
            foreach ($entries as $entry) {
                if ($entry->entry === ModuleBinding::ENTRY_MENU) {
                    $customMenu[] = [
                        'label' => $entry->title,
                        'url' => ['/web/site/entry', 'eid' => $entry->eid]
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
        $dataProvider = $service->createSearch()->search(); // TODO findAll()?
        return [
            'basic' => [
                'label' => '增强功能',
                'items' => [
                    ['label' => '自动回复', 'url' => [ '/web/rule/keyword' ]],
                    ['label' => '自定义菜单', 'url' => [ '/web/platform/menu' ]],
                    ['label' => '素材管理', 'url' => [ '/web/platform/material' ]]
                ],
            ],
            'fans' => [
                'label' => '粉丝管理',
                'items' => [
                    ['label' => '自动回复', 'url' => [ '/web/wechat/simulator/index' ]]
                ],
            ],
            'addon' => [
                'label' => '扩展模块',
                'items' => array_map(function($module) {
                    /** @var Module $module */
                    return ['label' => $module->title, 'url' => [$module->name . '/platform/cover']];
                }, $dataProvider->models),
            ],
            'settings' => [
                'label' => '基础配置',
                'items' => [
                    ['label' => '参数配置', 'url' => [ '/web/wechat/profile/remote' ]]
                ],
            ],
        ];
    }
}
