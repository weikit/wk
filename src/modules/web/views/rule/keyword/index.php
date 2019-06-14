<?php

use yii\helpers\Html;
use yii\grid\GridView;
$view->title = '自动回复';
?>

<div class="reply-keyword-index">

    <div class="page-header">
        <h4><?= Html::encode($view->title) ?></h4>
    </div>

    <?= $view->render('/rule/_nav') ?>

    <div class="mb20">
        <?= Html::a('添加关键字回复', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'uniacid',
            'name',
            'module',
            'status',
            //'displayorder',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>