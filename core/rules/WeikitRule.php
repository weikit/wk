<?php

namespace weikit\core\rules;

use yii\web\UrlRuleInterface;
use yii\base\BaseObject;

class WeikitRule extends BaseObject implements UrlRuleInterface
{
    public function createUrl($manager, $route, $params)
    {

    }

    public function parseRequest($manager, $request)
    {
        $c = $request->get('c');
        $a = $request->get('a');
        $do = $request->get('do', $a ? 'display' : null);

        $route = trim(implode('/', [is_admin() ? 'web' : 'app', $c, $a, $do]), '/');
        return [$route, []];
    }
}