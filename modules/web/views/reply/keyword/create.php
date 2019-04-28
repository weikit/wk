<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model weikit\models\Rule */

$this->title = '添加关键字回复';
$this->params['breadcrumbs'][] = ['label' => '关键字回复', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rule-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
