<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weikit\models\Module */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="module-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'version')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ability')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'author')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'settings')->textInput() ?>

    <?= $form->field($model, 'subscribes')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'handles')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'isrulefields')->textInput() ?>

    <?= $form->field($model, 'issystem')->textInput() ?>

    <?= $form->field($model, 'target')->textInput() ?>

    <?= $form->field($model, 'iscard')->textInput() ?>

    <?= $form->field($model, 'permissions')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title_initial')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'wxapp_support')->textInput() ?>

    <?= $form->field($model, 'welcome_support')->textInput() ?>

    <?= $form->field($model, 'oauth_type')->textInput() ?>

    <?= $form->field($model, 'webapp_support')->textInput() ?>

    <?= $form->field($model, 'phoneapp_support')->textInput() ?>

    <?= $form->field($model, 'account_support')->textInput() ?>

    <?= $form->field($model, 'xzapp_support')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
