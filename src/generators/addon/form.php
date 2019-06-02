<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\module\Generator */

?>
<div class="addon-form">

    <?= $form->field($generator, 'name'); ?>

    <?= $form->field($generator, 'identifie'); ?>

    <?= $form->field($generator, 'version'); ?>

    <?= $form->field($generator, 'ability'); ?>

    <?= $form->field($generator, 'description')->textarea(); ?>

    <?= $form->field($generator, 'author') ?>

    <?= $form->field($generator, 'url') ?>

    <?= $form->field($generator, 'supports')->checkboxList([
          'wechat' => '微信公众号'
    ]); ?>

</div>