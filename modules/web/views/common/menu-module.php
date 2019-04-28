<?php
use yii\helpers\Html;
use weikit\widgets\CategoryMenu;
use weikit\services\MenuService;

/** @var \weikit\addon\Module $__module */
$__module = $app->controller->module;
?>
<div class="module-info text-center">
    <p class="module-img">
        <img class="thumbnail" style="margin:0px auto;" src="resource/images/nopic-account.png">
    </p>
    <p class="module-name "><?= Html::encode($__module->title) ?></p>
</div>

<?= CategoryMenu::widget([
    'items' => \Yii::createObject(MenuService::class)->getAddonModuleMenu($__module->id)
]) ?>

