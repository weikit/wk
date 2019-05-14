<?php
use yii\bootstrap\Nav;
?>
<div class="mb20">
    <?= Nav::widget([
        'options' => [
            'class' => 'nav-underline',
        ],
        'items' => [
            'wechat' => [
                'label' => '已安装',
                'url' => ['/web/module/index'],
            ],
            'wxapp' => [
                'label' => '已停用',
                'url' => ['/web/module/disabled' ],
            ],
            'aliapp' => [
                'label' => '可安装',
                'url' => ['/web/module/inactive'],
            ],
        ],
    ]) ?>
</div>