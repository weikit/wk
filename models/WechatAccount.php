<?php

namespace weikit\models;

use weikit\core\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%account_wechats}}".
 *
 * @property int $acid
 * @property int $uniacid
 * @property string $token
 * @property string $encodingaeskey
 * @property int $level
 * @property string $name
 * @property string $account
 * @property string $original
 * @property string $signature
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $username
 * @property string $password
 * @property int $lastupdate
 * @property string $key
 * @property string $secret
 * @property int $styleid
 * @property string $subscribeurl
 * @property string $auth_refresh_token
 */
class WechatAccount extends ActiveRecord
{
    /**
     * 普通订阅号
     */
    const LEVEL_SUBSCRIBE = 1;
    /**
     * 普通服务号
     */
    const LEVEL_SERVICE = 2;
    /**
     * 认证订阅号
     */
    const LEVEL_SUBSCRIBE_VERIFY = 3;
    /**
     * 认证服务号
     */
    const TYPE_SERVICE_VERIFY = 4;

    /**
     * 公众号类型列表
     * @var array
     */
    public static $levels = [
        self::LEVEL_SUBSCRIBE => '普通订阅号',
        self::LEVEL_SERVICE => '认证订阅号',
        self::LEVEL_SUBSCRIBE_VERIFY => '认证订阅号',
        self::TYPE_SERVICE_VERIFY => '认证服务号/认证媒体/政府订阅号',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%account_wechats}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['acid', 'uniacid', 'name', 'account', 'original', 'level', 'key', 'secret'], 'required'],
            [['acid', 'uniacid', 'styleid'], 'integer'],
            [['level'], 'in', 'range' => array_keys(WechatAccount::$levels)],
            [['token'], 'string', 'max' => 32],
            [['signature'], 'string', 'max' => 100],
            [['original', 'key', 'secret'], 'string', 'max' => 50],
            [['encodingaeskey'], 'string', 'max' => 255],
            [['acid'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'acid' => 'Acid',
            'uniacid' => 'Uniacid',
            'token' => 'Token',
            'encodingaeskey' => 'Encodingaeskey',
            'level' => '公众号类型',
            'name' => '公众号名称',
            'account' => '公众号账号',
            'original' => '原始ID',
            'key' => 'AppID',
            'secret' => 'AppSecret',
            'signature' => 'Signature',
            'country' => 'Country',
            'province' => 'Province',
            'city' => 'City',
            'username' => 'Username',
            'password' => 'Password',
            'lastupdate' => 'Lastupdate',

            'styleid' => 'Styleid',
            'subscribeurl' => 'Subscribeurl',
            'auth_refresh_token' => 'Auth Refresh Token',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUniAccount()
    {
        return $this->hasOne(UniAccount::class, ['uniacid' => 'uniacid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelationAccount()
    {
        return $this->hasOne(Account::class, ['acid' => 'acid']);
    }

    /**
     *
     * @return string
     * @throws \yii\base\Exception
     */
    public static function generateToken()
    {
        return Yii::$app->security->generateRandomString(32);
    }

    /**
     * @return string
     * @throws \yii\base\Exception
     */
    public static function generateEncodingAesKey()
    {
        return Yii::$app->security->generateRandomString(43);
    }
}
