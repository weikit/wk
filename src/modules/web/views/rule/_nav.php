<?php
use yii\bootstrap\Nav;
?>
<?= Nav::widget([ // move to MenuService
    'options' => [
        'class' => 'nav-underline mb20'
    ],
    'items' => [
        ['label' => '关键字回复', 'url' => ['/web/rule/keyword/index']],
        ['label' => '非关键字回复', 'url' => ['/web/rule/special/index']],
        ['label' => '首词访问回复', 'url' => ['/web/rule/special']],
        ['label' => '默认回复', 'url' => ['/web/rule/special'], 'items' => [
            ['label' => '关键字回复', 'url' => ['/web/rule/keyword']],
        ]]
    ]
]) ?>