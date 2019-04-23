<?php
use yii\bootstrap\Nav;
use weikit\services\MenuService;

/* @var MenuService $menuService */
$menuService = Yii::createObject(MenuService::class);

?>
<?php include $this->template('common/header-base', TEMPLATE_INCLUDEPATH) ?>
<div data-skin="default" class="skin-default">
    <div class="main">
        <div class="container-fluid">
            <nav class="navbar navbar-default" style="margin-top: 15px;">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed"
                                data-toggle="collapse"
                                data-target="#bs-example-navbar-collapse-1"
                                aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="<?= $app->request->baseUrl ?>"><?= $app->name ?></a>
                    </div>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <?= Nav::widget([
                            'options' => ['class' => 'navbar-nav'],
                            'items'   => Yii::createObject(MenuService::class)->getWebNavMenu(),
                        ]) ?>

                        <?= Nav::widget([
                            'options' => ['class' => 'navbar-nav navbar-right'],
                            'items'   => Yii::createObject(MenuService::class)->getWebRightNavMenu(),
                        ]) ?>
                    </div>
                </div>
            </nav>
            <div class="content">
                <div class="row">
                <?php if (property_exists($app->controller, 'frame') && $app->controller->frame): ?>
                    <div class="col-md-2">
                        <?php include $this->template('common/header-' . $app->controller->frame, TEMPLATE_INCLUDEPATH) ?>
                    </div>
                    <div class="col-md-10">
                <?php else: ?>
                    <div class="col-md-12">
                <?php endif ?>