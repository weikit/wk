<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model weikit\models\WechatAccount */

$this->title = $model->name;
\yii\web\YiiAsset::register($this);
?>
<div class="account-wechat-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->acid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->acid], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'acid',
            'uniacid',
            'token',
            'encodingaeskey',
            'level',
            'name',
            'account',
            'original',
            'signature',
            'country',
            'province',
            'city',
            'username',
            'password',
            'lastupdate',
            'key',
            'secret',
            'styleid',
            'subscribeurl',
            'auth_refresh_token',
        ],
    ]) ?>

</div>
