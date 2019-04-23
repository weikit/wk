<?php
use yii\helpers\Html;
use yii\bootstrap\Tabs;

?>

<div class="page-header">
    <b>自动回复</b>
</div>
<?= Tabs::widget([
    'items' => [
        ['label' => '关键字回复', 'url' => ['/web/platform/reply/keyword']],
        ['label' => '非关键字回复', 'url' => ['/web/platform/reply/special']],
        ['label' => '首词访问回复', 'url' => ['/web/platform/reply/special']],
        ['label' => '默认回复', 'url' => ['/web/platform/reply/special']]
    ]
]) ?>