<?php

namespace weikit\modules\web\controllers;

use weikit\services\ModuleService;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SiteController extends Controller
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

    /**
     * @param string|int $eid
     * @param string|null $m
     * @param string|null $do
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionEntry($eid = null, $m = null, $do = null)
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