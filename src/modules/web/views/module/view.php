<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model weikit\models\Module */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Modules', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="module-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->mid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->mid], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'mid',
            'name',
            'type',
            'title',
            'version',
            'ability',
            'description',
            'author',
            'url:url',
            'settings',
            'subscribes',
            'handles',
            'isrulefields',
            'issystem',
            'target',
            'iscard',
            'permissions',
            'title_initial',
            'wxapp_support',
            'welcome_support',
            'oauth_type',
            'webapp_support',
            'phoneapp_support',
            'account_support',
            'xzapp_support',
        ],
    ]) ?>

</div>
