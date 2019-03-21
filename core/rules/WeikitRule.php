<?php

namespace weikit\core\rules;

use yii\web\UrlRuleInterface;
use yii\base\BaseObject;

class WeikitRule extends BaseObject implements UrlRuleInterface
{

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        // TODO 解决UrlManager不能设置baseUrl的问题
        $segments = explode('/', $route);
        if (isset($segments[0]) && in_array($segments[0], ['web', 'app'])) {
            $params = array_merge($params, [
                'c' => $segments[1] ?? null,
                'a' => $segments[2] ?? null,
                'do' => $segments[3] ?? null,
            ]);
            return '?' . http_build_query($params);
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        $c = $request->get('c');
        $a = $request->get('a');
        $do = $request->get('do');

        $route = trim(implode('/', [is_admin() ? 'web' : 'app', $c, $a, $do]), '/');
        return [$route, []];
    }
}