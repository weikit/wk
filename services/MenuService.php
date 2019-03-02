<?php

namespace weikit\services;

use weikit\core\service\BaseService;
use yii\helpers\ArrayHelper;

class MenuService extends BaseService
{
    /**
     * 通过关键字获取菜单数据
     *
     * @param $key
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
     * @return array
     */
    public function getModuleMenu($mid)
    {
        return [
            'wxapp' => [
                'label' => '微信小程序',
                'url' => ['/web/account/wxapp'],
                'items' => [
                    'aliapp' => [
                        'label' => '支付宝小程序1',
                        'url' => ['/web/account/aliapp'],
                    ],
                ]
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
}