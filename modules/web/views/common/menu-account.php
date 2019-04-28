<?php

use yii\helpers\Html;
use weikit\models\Account;
use weikit\models\WechatAccount;
use weikit\widgets\CategoryMenu;
use weikit\services\MenuService;
use weikit\services\AccountService;

/** @var Account $__account */
$__account = Yii::createObject(AccountService::class)->managing();
?>
<div class="account-info text-center">
    <p class="account-img">
        <img class="thumbnail" style="margin:0px auto;" src="resource/images/nopic-account.png">
    </p>
    <p class="account-name "><?= Html::encode($__account->uniAccount->name) ?></p>
    <p class="account-type">
    <?php if ($__account->type === Account::TYPE_WECHAT): ?>
        <span class="account-level"><?= WechatAccount::$levels[$__account->wechatAccount->level] ?></span>
    <?php endif ?>
        <?php if ($__account->isconnect): ?>
            <span class="label label-success">已接入</>
        <?php else: ?>
            <span class="label label-danger">未接入</span>
            <a href="#"> 立即接入</a>
        <?php endif ?>
    </p>
</div>
<?php if ($__account->type === Account::TYPE_WECHAT): ?>
    <?= CategoryMenu::widget([
        'items' => \Yii::createObject(MenuService::class)->getWechatAccountCategoryMenu()
    ]) ?>
<?php endif ?>
