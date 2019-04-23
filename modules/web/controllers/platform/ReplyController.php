<?php

namespace weikit\modules\web\controllers\platform;

use weikit\modules\web\Controller;

class ReplyController extends Controller
{
    /**
     * @var string
     */
    public $frame = 'account';
    /**
     * @var string
     */
    public $defaultAction = 'keyword';

    public function actionKeyword()
    {
        return $this->render('keyword');
    }
}