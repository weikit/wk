<?php
namespace weikit\modules\web\controllers;

use weikit\modules\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}