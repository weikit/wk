<?php

namespace weikit\modules\web\controllers;

use yii\web\Controller;

class EmulatorController extends Controller
{
    public function actionWechat()
    {
        return $this->render('wechat');
    }
}