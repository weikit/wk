<?php

namespace weikit\services;

use weikit\models\ModuleBinding;
use Yii;
use yii\helpers\ArrayHelper;
use weikit\core\service\BaseService;

class MenuService extends BaseService
{
    /**
     * 通过关键字获取菜单数据
     *
     * @param $key
     *
     * @return array
     */
    public function getMenuByKey($key)
    {
        $data = explode(':', $key);
        $method = ArrayHelper::remove($data, 0);
        $args = array_values($data);

        return call_user_func_array([$this, 'get' . $method . 'Menu'], $args);
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
        return Yii::$app->cache->getOrSet('menu_module:' . $moduleName, function() use ($moduleName) {
            /* @var $service ModuleService */
            $service = Yii::createObject(ModuleService::class);
            $module = $service->findByName($moduleName);
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
}