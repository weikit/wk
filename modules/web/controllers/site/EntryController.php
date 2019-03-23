<?php

namespace weikit\modules\web\controllers\site;

use weikit\core\addon\SiteAction;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use weikit\services\ModuleService;

class EntryController extends Controller
{
    /**
     * @var ModuleService
     */
    protected $service;

    /**
     * @inheritdoc
     */
    public function __construct($id, $module, ModuleService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function createAction2($id)
    {
        if ($id === '') {
            $id = $this->defaultAction;
        }

        $request = Yii::$app->request;
        $cache = Yii::$app->cache;

        if ($eid = (int) $request->get('eid')) {
            $cacheKey = 'cache_entry_' . $eid . '_module';
            $moduleName = $cache->get($cacheKey);
            if (!$moduleName) {
                $entry = $this->service->findEntryByEid($eid);
                $moduleName = $entry->module;
                $cache->set($cacheKey, $moduleName);
            }
        }

        if (empty($moduleName = $request->get('m'))) {
            throw new NotFoundHttpException('The entry of addon module is not found');
        }

        return $this->service->instanceActionSite($moduleName);
    }

    public function actions1()
    {
        $request = Yii::$app->request;
        $cache = Yii::$app->cache;

        $eid = (int) $request->get('eid');
        $cacheKey = 'cache_entry_' . $eid . '_module';
        $moduleName = $cache->get($cacheKey);
        if ($moduleName) {
            $cacheKey = 'cache_module_' . $moduleName . '_entries';
            $entries = $cache->get($cacheKey);
//            if ($entries)
        }
//        $cacheKey = 'action_site_entry' .
        if ($eid = (int) $request->get('eid')) {
            $entry = $this->service->findEntryByEid($eid, [
                'query' => function($query) {
                    $query->with('relationModule', 'relationModule.entries');
                }
            ]);
            $entries = $entry->relationModule->entries;
        } elseif($moduleName = $request->get('m')) {
            $module = $this->service->findByName($moduleName, [
                'query' => function($query) {
                    $query->with('entries');
                }
            ]);
            $entries = $module->entries;
        } else {
            throw new NotFoundHttpException('The entry of addon module is not found');
        }
        $actions = ArrayHelper::map($entries, 'do', function($entry) {
            return [
                'class' => SiteAction::class,
                'moduleName' => $entry->module,
            ];
        });

        return $actions;
    }

    /**
     * @param string|int $eid
     * @param string|null $m
     * @param string|null $do
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionIndex($eid = null, $m = null, $do = null)
    {
        if ($eid) {
            $entry = $this->service->findEntryByEid($eid);
            $module = $entry->relationModule;
        } else {
            $entry = $this->service->findEntryBy(['module' => $m, 'do' => $do], ['exception' => false]);
            $module = $entry ? $entry->relationModule : $this->service->findByName($m);
        }
        if (empty($entry)) {
            throw new NotFoundHttpException('The entry of addon module is not found');
        }

        // TOOD 兼容语法. 移除并更完美的兼容
        global $_GPC;
        $_GPC['state'] = $entry->state;
        $_GPC['m'] = $entry->module;
        $_GPC['do'] = $entry->do;

        ob_start();
        ob_implicit_flush(false);
        $method = 'doWeb' . ucfirst($entry->do);
        echo $this->service
            ->instanceSite($module)
            ->$method();

        return ob_get_clean();
    }
}