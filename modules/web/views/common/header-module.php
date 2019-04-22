<?php
use weikit\widgets\CategoryMenu;
?>

<?= CategoryMenu::widget([
    'items' => $app->controller->module->getMenu()
]) ?>

