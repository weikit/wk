<?php

namespace weikit\addon\controllers\web;

use yii\web\Controller;

class PlatformController extends Controller
{
    /**
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