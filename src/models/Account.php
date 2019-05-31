<?php

namespace weikit\models;

use Yii;
use weikit\core\db\ActiveRecord;

/**
 * This is the model class for table "{{%account}}".
 *
 * @property int $acid
 * @property int $uniacid
 * @property string $hash
 * @property int $type
 * @property int $isconnect
 * @property int $isdeleted
 * @property int $endtime
 *
 * @property WechatAccount $wechatAccount
 * @property UniAccount $uniAccount
 */
class Account extends ActiveRecord
{
    /**
     * 微信公众号
     */
    const TYPE_WECHAT = ACCOUNT_TYPE_OFFCIAL_NORMAL;
    /**
     * 支持的类型
     *
     * @var array
     */
    public static $types = [
        self::TYPE_WECHAT => '微信公众号',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%account}}';
    }

    /**
     * @return AccountQuery
     */
    public static function find()
    {
        return Yii::createObject(AccountQuery::class, [get_called_class()]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uniacid', 'hash', 'type', 'isconnect', 'isdeleted', 'endtime'], 'required'],
            [['type'], 'in', 'range' => static::$types],
            [['uniacid', 'type', 'isconnect', 'isdeleted', 'endtime'], 'integer'],
            [['hash'], 'string', 'max' => 8],
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
            'hash' => 'Hash',
            'type' => 'Type',
            'isconnect' => 'Isconnect',
            'isdeleted' => 'Isdeleted',
            'endtime' => 'Endtime',
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
    public function getWechatAccount()
    {
        return $this->hasOne(WechatAccount::class, ['acid' => 'acid']);
    }

    /**
     * @return string
     */
    public static function generateHash()
    {
        return Yii::$app->security->generateRandomString(8);
    }
}
