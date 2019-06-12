<?php

namespace weikit\core\addon\controllers\web\platform;

use weikit\modules\web\Controller;

class ReplyController extends Controller
{
    /**
     * @var string
     */
    public $menu = 'common/menu-account';

    public function actionIndex()
    {
        return $this->render('index');
    }
}