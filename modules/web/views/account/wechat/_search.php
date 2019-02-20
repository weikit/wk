<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weikit\modules\web\models\AccountSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="account-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'acid') ?>

    <?= $form->field($model, 'uniacid') ?>

    <?= $form->field($model, 'hash') ?>

    <?= $form->field($model, 'type') ?>

    <?= $form->field($model, 'isconnect') ?>

    <?php // echo $form->field($model, 'isdeleted') ?>

    <?php // echo $form->field($model, 'endtime') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
