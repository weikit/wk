<?php

namespace weikit\models;

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
class AccountWechat extends \yii\db\ActiveRecord
{
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
            [['acid', 'uniacid', 'token', 'encodingaeskey', 'level', 'name', 'account', 'original', 'signature', 'country', 'province', 'city', 'username', 'password', 'lastupdate', 'key', 'secret', 'styleid', 'subscribeurl', 'auth_refresh_token'], 'required'],
            [['acid', 'uniacid', 'level', 'lastupdate', 'styleid'], 'integer'],
            [['token', 'password'], 'string', 'max' => 32],
            [['encodingaeskey', 'auth_refresh_token'], 'string', 'max' => 255],
            [['name', 'account', 'username'], 'string', 'max' => 30],
            [['original', 'key', 'secret'], 'string', 'max' => 50],
            [['signature'], 'string', 'max' => 100],
            [['country'], 'string', 'max' => 10],
            [['province'], 'string', 'max' => 3],
            [['city'], 'string', 'max' => 15],
            [['subscribeurl'], 'string', 'max' => 120],
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
            'level' => 'Level',
            'name' => 'Name',
            'account' => 'Account',
            'original' => 'Original',
            'signature' => 'Signature',
            'country' => 'Country',
            'province' => 'Province',
            'city' => 'City',
            'username' => 'Username',
            'password' => 'Password',
            'lastupdate' => 'Lastupdate',
            'key' => 'Key',
            'secret' => 'Secret',
            'styleid' => 'Styleid',
            'subscribeurl' => 'Subscribeurl',
            'auth_refresh_token' => 'Auth Refresh Token',
        ];
    }
}
