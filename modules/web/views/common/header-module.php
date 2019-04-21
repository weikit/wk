<?php
use weikit\widgets\NavMenu;
?>

<?= NavMenu::widget([
    'items' => $app->controller->module->getMenu()
]) ?>

