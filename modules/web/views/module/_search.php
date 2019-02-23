<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weikit\models\search\ModuleSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="module-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'mid') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'type') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'version') ?>

    <?php // echo $form->field($model, 'ability') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'author') ?>

    <?php // echo $form->field($model, 'url') ?>

    <?php // echo $form->field($model, 'settings') ?>

    <?php // echo $form->field($model, 'subscribes') ?>

    <?php // echo $form->field($model, 'handles') ?>

    <?php // echo $form->field($model, 'isrulefields') ?>

    <?php // echo $form->field($model, 'issystem') ?>

    <?php // echo $form->field($model, 'target') ?>

    <?php // echo $form->field($model, 'iscard') ?>

    <?php // echo $form->field($model, 'permissions') ?>

    <?php // echo $form->field($model, 'title_initial') ?>

    <?php // echo $form->field($model, 'wxapp_support') ?>

    <?php // echo $form->field($model, 'welcome_support') ?>

    <?php // echo $form->field($model, 'oauth_type') ?>

    <?php // echo $form->field($model, 'webapp_support') ?>

    <?php // echo $form->field($model, 'phoneapp_support') ?>

    <?php // echo $form->field($model, 'account_support') ?>

    <?php // echo $form->field($model, 'xzapp_support') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
