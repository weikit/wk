<?php
namespace weikit\modules\web\controllers;

use weikit\modules\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $db = \Yii::$app->db;
        $command = $db->createCommand('select * from wp_users where user_login=:user_login', ['user_login' => 'admin']);
        $data = $command->queryOne();
        return $this->render('index');
    }
}