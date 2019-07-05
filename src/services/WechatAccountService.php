<?php

namespace weikit\services;

use Yii;
use yii\web\Request;
use weikit\core\Service;
use weikit\models\Account;
use weikit\models\UniAccount;
use weikit\models\WechatAccount;
use weikit\models\form\WechatAccountForm;
use weikit\models\search\WechatAccountSearch;

class WechatAccountService extends Service
{
    /**
     * @var string
     */
    public $modelClass = WechatAccount::class;

    /**
     * @param $acid
     * @param array $options
     *
     * @return WechatAccount|null
     * @throws \weikit\core\exceptions\ModelNotFoundException
     */
    public function findByAcid($id, array $options = [])
    {
        return $this->findBy(['wa.acid' => $id], array_merge($options, [
            'alias' => 'wa'
        ]));
    }

    /**
     * @param array $options2i
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function createSearch(array $options = [])
    {
        return Yii::createObject(array_merge($options, [
            'class' => WechatAccountSearch::class
        ]));
    }

    /**
     * 添加微信公众号账户
     *
     * @param Request|array $requestOrData
     *
     * @return WechatAccountForm|WechatAccount 添加成功返回Account, 失败返回AccountWechatForm
     */
    public function add($requestOrData)
    {
        /** @var WechatAccountForm $model */
        $model = Yii::createObject(WechatAccountForm::class);

        if ($model->loadFrom($requestOrData)) {
            return $this->addWechatAccount($model);
        }

        return $model;
    }

    /**
     * @param WechatAccountForm $form
     *
     * @return WechatAccount
     */
    protected function addWechatAccount(WechatAccountForm $form)
    {
        $form->tryValidate();

        /* @var $uniAccount UniAccount */
        $uniAccount = Yii::createObject(UniAccount::class);
        return $uniAccount->getDb()->transaction(function() use ($form, $uniAccount) {

            // 1. 创建UniAccount
            $uniAccount->setAttributes([
                'name' => $form->name,
                'description' => $form->description,
                'title_initial' => $uniAccount->defaultTitleInitial($form->name),
            ], false);
            $uniAccount->save(false);

            // 2. 创建Account
            /* @var $account Account */
            $account = Yii::createObject(Account::class);
            $account->setAttributes([
                'uniacid' => $uniAccount->uniacid,
                'hash' => Account::generateHash(),
                'type' => Account::TYPE_WECHAT,

            ], false);
            $account->save(false);

            // 3. 创建AccountWechat
            /* @var $wechatAccount WechatAccount */
            $wechatAccount = Yii::createObject(WechatAccount::class);
            $wechatAccount->setAttributes([
                'acid' => $account->acid,
                'uniacid' => $uniAccount->uniacid,
                'name' => $form->name,
                'account' => $form->account,
                'original' => $form->original,
                'level' => $form->level,
                'key' => $form->key,
                'secret' => $form->secret,
                'token' => WechatAccount::generateToken(),
                'encodingaeskey' => WechatAccount::generateEncodingAesKey(),
            ], false);
            $wechatAccount->save(false);

            $account->populateRelation('uniAccount', $uniAccount);
            $account->populateRelation('wechatAccount', $wechatAccount);

            // 成功返回WechatAccount
            return $wechatAccount;
        });
    }

    /**
     * @param $acid
     * @param $requestOrData
     *
     * @return WechatAccount
     * @throws \weikit\core\exceptions\ModelNotFoundException
     * @throws \weikit\core\exceptions\ModelValidationException
     */
    public function editByAcid($acid, $requestOrData)
    {
        $model = $this->findByAcid($acid);
        if ($model->loadFrom($requestOrData)) {
            $model->trySave();
        }
        return $model;
    }

    /**
     * @param $acid
     *
     * @return false|int
     * @throws \weikit\core\exceptions\ModelNotFoundException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteByAcid($acid)
    {
        $model = $this->findByAcid($acid);

        return $model->delete();
    }
}