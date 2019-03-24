<?php

namespace weikit\addon\web\controllers;

use yii\web\Controller;

class PlatformController extends Controller
{
    /**
     * @param null $m
     * @param null $eid
     *
     * @return string
     */
    public function actionCover()
    {
        return $this->render('cover', [
            'module' => $this->module->model,
        ]);
    }
}