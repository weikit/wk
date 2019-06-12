<?php
namespace weikit\core\addon\controllers\web\platform;

use weikit\core\addon\Controller;

class CoverController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'module' => $this->module->model,
        ]);
    }
}