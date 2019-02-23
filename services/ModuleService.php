<?php

namespace weikit\services;

use Yii;
use weikit\models\Module;
use yii\data\ActiveDataProvider;
use weikit\core\service\BaseService;
use weikit\models\search\ModuleSearch;

class ModuleService extends BaseService
{

    /**
     * @param $acid
     *
     * @return WechatAccount|null
     * @throws \weikit\core\exceptions\ModelNotFoundException
     */
    public function findByAcid($id)
    {
        return Module::tryFindOne($id);
    }

    /**
     * @param Request|array $requestOrData
     *
     * @return array
     */
    public function search($requestOrData)
    {
        $model = Yii::createObject(ModuleSearch::class);

        $query = Module::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->isModelLoad($model, $requestOrData);
        if ($model->validate()) {
            $query->andFilterWhere([
                'mid' => $model->mid,
                'settings' => $model->settings,
                'isrulefields' => $model->isrulefields,
                'issystem' => $model->issystem,
                'target' => $model->target,
                'iscard' => $model->iscard,
                'wxapp_support' => $model->wxapp_support,
                'welcome_support' => $model->welcome_support,
                'oauth_type' => $model->oauth_type,
                'webapp_support' => $model->webapp_support,
                'phoneapp_support' => $model->phoneapp_support,
                'account_support' => $model->account_support,
                'xzapp_support' => $model->xzapp_support,
            ])
            ->andFilterWhere(['like', 'name', $model->name])
            ->andFilterWhere(['like', 'type', $model->type])
            ->andFilterWhere(['like', 'title', $model->title])
            ->andFilterWhere(['like', 'version', $model->version])
            ->andFilterWhere(['like', 'ability', $model->ability])
            ->andFilterWhere(['like', 'description', $model->description])
            ->andFilterWhere(['like', 'author', $model->author])
            ->andFilterWhere(['like', 'url', $model->url])
            ->andFilterWhere(['like', 'subscribes', $model->subscribes])
            ->andFilterWhere(['like', 'handles', $model->handles])
            ->andFilterWhere(['like', 'permissions', $model->permissions])
            ->andFilterWhere(['like', 'title_initial', $model->title_initial]);
        }

        return [
            'searchModel' => $model,
            'dataProvider' => $dataProvider
        ];
    }

    public function inactive()
    {
        $list = Yii::$app->addon->findAvailable();
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