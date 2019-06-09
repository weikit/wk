<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this \yii\web\View */
/* @var $model \weikit\models\WechatAccount */
/* @var $dataProvider \yii\data\ArrayDataProvider */
?>
<div class="module-index">
    <p>
        <?= Html::a('Create Module', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= $view->render('_nav') ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'title',
            'name',
            'version',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{install}',
                'buttons' => [
                    'install' => function ($url, $model, $key) {
                        return Html::a('安装模块', wurl('module/activate', ['name' => $model['name']]));
                    }
                ]

            ],
        ],
    ]) ?>
</div>
