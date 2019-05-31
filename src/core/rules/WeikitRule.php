<?php

namespace weikit\core\rules;

use Yii;
use yii\web\Request;
use yii\base\BaseObject;
use yii\web\UrlRuleInterface;
use weikit\services\ModuleService;

class WeikitRule extends BaseObject implements UrlRuleInterface
{
    /**
     * 模块入口缓存
     */
    const CACHE_ADDON_MODULE_ENTY = 'cache_addon_module_entry';
    const CACHE_ADDON_ACCOUNT = 'cache_addon_account';

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        // TODO 解决必须全伪静态形式配备形式, 兼容所有url方式
        $segments = explode('/', $route);

        if ( ! in_array($segments[0], ['web', 'app'])) {
            return false;
        }

        // TODO 优化API链接
        if ($segments[0] === 'app' && ! empty($segments[1]) && $segments[1] === 'api') {
            return '../api.php?' . http_build_query($params);
        }

        $c = $segments[1] ?? null;
        $m = $params['m'] ?? null;

        if (empty($m) && $c) { // 未指定模块从模块中查询$c是否为模块名
            $rootModule = Yii::$app->getModule($segments[0]);
            if ($rootModule->hasModule($c)) {
                $m = $segments[1] ?? null;
                unset($segments[1]);
                $segments = array_values($segments);
                $c = $segments[1] ?? null;
            }
        }

        return '?' . http_build_query(array_merge([
            'm'  => $m,
            'c'  => $c,
            'a'  => $segments[2] ?? null,
            'do' => $segments[3] ?? null,
        ], $params));
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        if (defined('IN_SYS')) {
            return $this->parseWeb($request);
        } elseif (defined('IN_MOBILE')) {
            return $this->parseApp($request);
        } elseif (defined('IN_API')) {
            return $this->parseApi($request);
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function parseAddonRoute($request)
    {
        return [
            'm'  => $request->get('m'),
            'c'  => strtolower($request->get('c')),
            'a'  => strtolower($request->get('a')),
            'do' => strtolower($request->get('do'))
        ];
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function parseWeb($request)
    {
        ['m' => $m, 'c' => $c, 'a' => $a, 'do' => $do] = $this->parseAddonRoute($request);

        if ($c === 'site' && $a === 'entry' && $eid = $request->get('eid')) {
            $_GET = array_merge($_GET, ['m' => $m, 'do' => $do] = $this->findEntryDataByEid($eid));
            $c = null;
        }

        \We8::initWeb();

        return [implode('/', array_filter(['web', $m, $c, $a, $do])), []];
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function parseApp($request)
    {
        ['m' => $m, 'c' => $c, 'a' => $a, 'do' => $do] = $this->parseAddonRoute($request);

        if ($c === 'entry' && $eid = $request->get('eid')) {
            $_GET = array_merge($_GET, ['m' => $m, 'do' => $do] = $this->findEntryDataByEid($eid));
        }

        \We8::initApp();

        return [implode('/', array_filter(['app', $m, $c, $a, $do])), []];
    }

    protected function parseApi($request)
    {
        \We8::initApp();

        return ['/app/api', []];
    }

    /**
     * @param int $eid
     *
     * @return array|mixed
     * @throws \yii\base\InvalidConfigException
     */
    protected function findEntryDataByEid($eid)
    {
        // TODO cache dependency
        return Yii::$app->cache->getOrSet(self::CACHE_ADDON_MODULE_ENTY . ':' . (int)$eid, function() use ($eid) {
            /* @var $service ModuleService */
            $service = Yii::createObject(ModuleService::class);
            $entry = $service->findEntryByEid($eid);  // TODO 优化统一结构
            return [
                'm'  => $entry->module,
                'do' => $entry->do,
            ];
        });

    }
}