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
            $do = $entry->do;
        } else {
            $entry = $this->service->findEntryBy(['module' => $m, 'do' => $do], ['exception' => false]);
            $module = $entry ? $entry->relationModule : $this->service->findByName($m);
        }
        if (empty($do)) {
            throw new NotFoundHttpException('The method "do" of module is missing');
        }

        ob_start(); // TODO
        ob_implicit_flush(false);

        $method = 'doWeb' . ucfirst($do);
        echo $this->service
            ->instanceSite($module)
            ->$method();

        return ob_get_clean();
    }
}