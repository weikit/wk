<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weikit\models\WechatAccountSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="account-wechat-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'acid') ?>

    <?= $form->field($model, 'uniacid') ?>

    <?= $form->field($model, 'token') ?>

    <?= $form->field($model, 'encodingaeskey') ?>

    <?= $form->field($model, 'level') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'account') ?>

    <?php // echo $form->field($model, 'original') ?>

    <?php // echo $form->field($model, 'signature') ?>

    <?php // echo $form->field($model, 'country') ?>

    <?php // echo $form->field($model, 'province') ?>

    <?php // echo $form->field($model, 'city') ?>

    <?php // echo $form->field($model, 'username') ?>

    <?php // echo $form->field($model, 'password') ?>

    <?php // echo $form->field($model, 'lastupdate') ?>

    <?php // echo $form->field($model, 'key') ?>

    <?php // echo $form->field($model, 'secret') ?>

    <?php // echo $form->field($model, 'styleid') ?>

    <?php // echo $form->field($model, 'subscribeurl') ?>

    <?php // echo $form->field($model, 'auth_refresh_token') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
