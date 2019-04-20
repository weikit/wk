<?php
namespace weikit\addon\controllers\web;

use weikit\modules\web\Controller;

class PlatformController extends Controller
{
    /**
     * @var bool 
     */
    public $enableAccountManageCheck = true;

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

    public function actionReply()
    {
        return $this->render('reply');
    }
}