<?php

namespace weikit\controllers;

use yii\rest\Controller;
use weikit\services\AccountService;

class ApiController extends Controller
{
    /**
     * @var AccountService
     */
    protected $service;

    public function __construct($id, $module, AccountService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex($hash)
    {
        $account = $this->service->findByHash($hash);
    }
}