<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model weikit\models\Rule */
/* @var $keywordModel \weikit\models\RuleKeyword */

$this->title = '添加关键字回复';
$this->params['breadcrumbs'][] = ['label' => '关键字回复', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="reply-keyword-create" id="app">
    <?= $this->render('_form', [
        'model' => $model,
        'keywordModel' => $keywordModel,
    ]) ?>

</div>