<?php
namespace weikit\addon\controllers\web;

use weikit\addon\Controller;

class HomeController extends Controller
{
    /**
     * @var bool
     */
    public $enableAccountManageCheck = true;

    public function actionIndex()
    {
        return '123';
    }
}