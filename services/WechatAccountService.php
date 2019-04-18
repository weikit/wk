<?php

namespace weikit\services;

use Yii;
use yii\web\Request;
use yii\data\ActiveDataProvider;
use weikit\models\Account;
use weikit\models\UniAccount;
use weikit\models\WechatAccount;
use weikit\core\service\BaseService;
use weikit\models\form\WechatAccountForm;
use weikit\models\search\WechatAccountSearch;

class WechatAccountService extends BaseService
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
     * @param Request|array $requestOrData
     *
     * @return array
     */
    public function search($requestOrData)
    {
        $model = Yii::createObject(WechatAccountSearch::class);

        $query = WechatAccount::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->isModelLoad($model, $requestOrData);
        if ($model->validate()) {
            $query->andFilterWhere([
                'acid' => $model->acid,
                'uniacid' => $model->uniacid,
                'level' => $model->level,
            ])
            ->andFilterWhere(['like', 'name', $model->name]);
        }

        return [
            'searchModel' => $model,
            'dataProvider' => $dataProvider
        ];
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

        if ($this->isModelLoad($model, $requestOrData)) {
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
        if ($this->isModelLoad($model, $requestOrData)) {
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