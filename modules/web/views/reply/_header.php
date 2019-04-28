<?php
use yii\bootstrap\Nav;
?>
<?= Nav::widget([
    'options' => [
        'class' => 'nav-underline mb20'
    ],
    'items' => [
        ['label' => '关键字回复', 'url' => ['/web/reply/keyword/index']],
        ['label' => '非关键字回复', 'url' => ['/web/reply/special/index']],
        ['label' => '首词访问回复', 'url' => ['/web/reply/special']],
        ['label' => '默认回复', 'url' => ['/web/reply/special'], 'items' => [
            ['label' => '关键字回复', 'url' => ['/web/reply/keyword']],
        ]]
    ]
]) ?>