<?php

namespace weikit\modules\web\controllers;

use weikit\services\ModuleService;
use yii\web\Controller;

class PlatformController extends Controller
{

    /**
     * @var ModuleService
     */
    protected $moduleService;

    /**
     * @inheritdoc
     */
    public function __construct($id, $module, ModuleService $moduleService, $config = [])
    {
        $this->moduleService = $moduleService;
        parent::__construct($id, $module, $config);
    }

    public function actionCover($m = null, $eid = null)
    {
        if ($m) {
            $module = $this->moduleService->findByName($m);
        } else {
            // TODO eid
        }

        return $this->render('cover', [
            'module' => $module,
        ]);
    }
}