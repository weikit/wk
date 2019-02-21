<?php

namespace weikit\models\form;

use Yii;
use yii\base\Model;
use weikit\models\Account;
use weikit\models\UniAccount;
use weikit\models\WechatAccount;
use weikit\core\exceptions\ModelValidationException;

class WechatAccountForm extends Model
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $description;
    /**
     * @var string
     */
    public $account;
    /**
     * @var string
     */
    public $original;
    /**
     * @var int
     */
    public $level;
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $secret;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'account', 'original', 'level', 'key', 'secret'], 'required'],
            [['level'], 'in', 'range' => array_keys(WechatAccount::$levels)],
            [['description'], 'default', 'value' => ''],
            [['description'], 'string', 'max' => 255],
        ];
    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => '公众号名称',
            'description' => '描述',
            'account' => '公众号账号',
            'original' => '原始ID',
            'level' => '公众号类型',
            'key' => 'AppID',
            'secret' => 'AppSecret',
        ];
    }

    /**
     * 创建微信公众号账号
     *
     * @return Account
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function tryAdd()
    {
        if (!$this->validate()) {
            throw new ModelValidationException($this);
        }

        /* @var $uniAccount UniAccount */
        $uniAccount = Yii::createObject(UniAccount::class);
        return $uniAccount->getDb()->transaction(function() use ($uniAccount) {

            // 1. 创建UniAccount
            $uniAccount->setAttributes([
                'name' => $this->name,
                'description' => $this->description,
                'title_initial' => $uniAccount->defaultTitleInitial($this->name),

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
            $wechatAccount = Yii::createObject(WechatAccount::class);
            $wechatAccount->setAttributes([
                'acid' => $account->acid,
                'uniacid' => $uniAccount->uniacid,
                'name' => $this->name,
                'account' => $this->account,
                'original' => $this->original,
                'level' => $this->level,
                'key' => $this->key,
                'secret' => $this->secret,
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
}