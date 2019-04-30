<?php

use yii\helpers\Html;
use weikit\models\Rule;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weikit\models\Rule */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="rule-form">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal'
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'module')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status', ['inline' => true])->radioList(Rule::$statuses) ?>

    <?= $form->field($model, 'displayorder')->textInput() ?>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6">
            <?= Html::submitButton($model->isNewRecord ? '创建回复规则' : '修改回复规则', [
                'class' => 'btn btn-block ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')
            ]) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
