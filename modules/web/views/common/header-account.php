<?php

use yii\helpers\Html;
use weikit\models\Account;
use weikit\models\WechatAccount;
use weikit\widgets\CategoryMenu;
use weikit\services\MenuService;
use weikit\services\ModuleService;
use weikit\services\AccountService;

/* @var AccountService $accountService */
$accountService = Yii::createObject(AccountService::class);
/* @var ModuleService $moduleService */
$moduleService = Yii::createObject(ModuleService::class);
/* @var MenuService $menuService */
$menuService = Yii::createObject(MenuService::class);

$account = $accountService->managing();
?>
<div class="account-info text-center">
    <p class="account-img">
        <img class="thumbnail" style="margin:0px auto;" src="resource/images/nopic-account.png">
    </p>
    <p class="account-name "><?= Html::encode($account->uniAccount->name) ?></p>
    <p class="account-type">
    <?php if ($account->type === Account::TYPE_WECHAT): ?>
        <span class="account-level"><?= WechatAccount::$levels[$account->wechatAccount->level] ?></span>
    <?php endif ?>
        <span class="account-isconnect">
        <?php if ($account->isconnect): ?>
            已接入
        <?php else: ?>
            未接入
            <a href="#" class="text-danger"> 立即接入</a>
        <?php endif ?>
        </span>
    </p>
</div>
<?php if ($account->type === Account::TYPE_WECHAT): ?>
    <?= CategoryMenu::widget([
        'items' => $menuService->getWechatAccountCategoryMenu()
    ]) ?>
<?php endif ?>
