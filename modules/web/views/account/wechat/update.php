<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model weikit\models\AccountWechat */

$this->title = 'Update Account Wechat: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Account Wechats', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->acid]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="account-wechat-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
