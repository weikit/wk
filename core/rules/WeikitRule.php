<?php

namespace weikit\core\rules;

use weikit\services\ModuleService;
use Yii;
use yii\base\BaseObject;
use yii\web\UrlRuleInterface;

class WeikitRule extends BaseObject implements UrlRuleInterface
{
    /**
     * 模块入口缓存
     */
    const CACHE_ADDON_MODULE_ENTY = 'cache_addon_module_entry';
    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        // TODO 解决UrlManager不能设置baseUrl的问题
        $segments = explode('/', $route);
        if (isset($segments[0]) && in_array($segments[0], ['web', 'app'])) {
            $params = array_merge([
                'c' => $segments[1] ?? null,
                'a' => $segments[2] ?? null,
                'do' => $segments[3] ?? null,
            ], $params);
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
        if ($eid = (int)$request->get('eid')) {
            $cache = Yii::$app->cache;
            if (!($m = $cache->get(self::CACHE_ADDON_MODULE_ENTY))) {
                /* @var $service ModuleService */
                $service = Yii::createObject(ModuleService::class);
                $entry = $service->findEntryByEid($eid);  // TODO 优化统一结构
                $m = $entry->module;
                // TODO cache dependency
                $cache->set(self::CACHE_ADDON_MODULE_ENTY, $m);
            }
        } else {
            $m = $request->get('m');
        }

        if (is_admin()) {
            $rootModule = 'web';
            if ($c === 'site' && $a === 'entry') {
                $a = null;
                $c = 'entry';
            }
        } else {
            $rootModule = 'app';

            if ($c == 'entry') {
                $a = null;
                $c = 'entry';
            }
        }

        $route = implode('/', array_filter([$rootModule, $m, $c, strtolower($a), strtolower($do)]));
        return [$route, []];
    }
}