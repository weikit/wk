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
            $cacheKey = self::CACHE_ADDON_MODULE_ENTY . ':' . $eid;
            if (!($data = $cache->get($cacheKey))) {
                /* @var $service ModuleService */
                $service = Yii::createObject(ModuleService::class);
                $entry = $service->findEntryByEid($eid);  // TODO 优化统一结构
                $data = [
                    'm' => $entry->module,
                    'do' => $entry->do
                ];
                // TODO cache dependency
                $cache->set($cacheKey, $data);
            }
            extract($data);
        } else {
            $m = $request->get('m');
        }

        $c = strtolower($c);
        $a = strtolower($a);
        $do = strtolower($do);

        // 路由兼容
        if (is_admin()) {
            $rootModule = 'web';
            if ($c === 'site' && $a === 'entry') { // 自定义功能入口
                $a = null;
                $c = 'entry';
            } elseif ($c == 'module' && $a = 'manage-account') { // 模块参数设置入口
                $a = null;
            }
        } else {
            $rootModule = 'app';
            if ($c == 'entry') { // 自定义功能入口
                $a = null;
            }
        }

        $route = implode('/', array_filter([$rootModule, $m, $c, $a, $do]));
        return [$route, []];
    }
}