<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weikit\models\search\RuleSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="rule-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'uniacid') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'module') ?>

    <?= $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'displayorder') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
