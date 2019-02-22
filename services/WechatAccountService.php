<?php

namespace weikit\services;

use Yii;
use yii\web\Request;
use weikit\models\Account;
use weikit\models\UniAccount;
use weikit\models\WechatAccount;
use weikit\core\service\BaseService;
use weikit\models\WechatAccountSearch;
use weikit\models\form\WechatAccountForm;

class WechatAccountService extends BaseService
{
    /**
     * @param $acid
     *
     * @return WechatAccount|null
     * @throws \weikit\core\exceptions\ModelNotFoundException
     */
    public function findByAcid($id)
    {
        return WechatAccount::tryFindOne($id);
    }

    /**
     * @param array $query
     *
     * @return array
     */
    public function search(array $query)
    {
        $searchModel = new WechatAccountSearch();
        $dataProvider = $searchModel->search($query);
        return compact('searchModel', 'dataProvider');
    }

    /**
     * 添加微信公众号账户
     *
     * @param Request|array $requestOrData
     *
     * @return AccountWechatForm|WechatAccount 添加成功返回Account, 失败返回AccountWechatForm
     */
    public function add($requestOrData)
    {
        $model = Yii::createObject(WechatAccountForm::class);

        if (
            $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '')
        ) {
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

                'groupid' => 0, // TODO remove
                'default_acid' => 0, // TODO remove
            ], false);
            $uniAccount->save(false);

            // 2. 创建Account
            /* @var $account Account */
            $account = Yii::createObject(Account::class);
            $account->setAttributes([
                'uniacid' => $uniAccount->uniacid,
                'type' => '',
                'hash' => Account::generateHash(),

                'isconnect' => 0, // TODO fix
                'isdeleted' => 0, // TODO fix
                'endtime' => 0, // TODO fix

                'type' => 1, // TODO remove or fix

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

                'signature' => '', // TODO remove or fix
                'country' => '', // TODO remove or fix
                'province' => '', // TODO remove or fix
                'city' => '', // TODO remove or fix
                'username' => '', // TODO remove or fix
                'password' => '', // TODO remove or fix
                'lastupdate' => 0, // TODO fix
                'styleid' => 0, // TODO remove or fix
                'subscribeurl' => '', // TODO remove or fix
                'auth_refresh_token' => '', // TODO remove or fix
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
        if (
            $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '')
        ) {
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