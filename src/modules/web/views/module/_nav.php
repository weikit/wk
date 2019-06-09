<?php
use yii\bootstrap\Nav;
?>
<div class="mb20">
    <?= Nav::widget([
        'options' => [
            'class' => 'nav-underline',
        ],
        'items' => [
            'active' => [
                'label' => '已安装',
                'url' => ['/web/module/index'],
            ],
            'inactive' => [
                'label' => '已停用',
                'url' => ['/web/module/index', 'status' => 1],
            ],
            'uninstalled' => [
                'label' => '未安装',
                'url' => ['/web/module/uninstalled'],
            ],
        ],
    ]) ?>
</div>