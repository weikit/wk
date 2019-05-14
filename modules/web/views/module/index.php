<?php
use yii\helpers\Html;
use yii\grid\GridView;
$view->title = '模块管理';
?>

<div class="module-index">

    <div class="page-header">
        <h4><?= Html::encode($view->title) ?></h4>
    </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= $view->render('_nav') ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            //'mid',
            'name',
            'type',
            'title',
            'version',
            //'ability',
            //'description',
            //'author',
            //'url:url',
            //'settings',
            //'subscribes',
            //'handles',
            //'isrulefields',
            //'issystem',
            //'target',
            //'iscard',
            //'permissions',
            //'title_initial',
            //'wxapp_support',
            //'welcome_support',
            //'oauth_type',
            //'webapp_support',
            //'phoneapp_support',
            //'account_support',
            //'xzapp_support',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]) ?>

</div>
