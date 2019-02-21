<?php

namespace weikit\models;

use weikit\core\db\ActiveRecord;
use Yii;

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
 */
class Account extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%account}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uniacid', 'hash', 'type', 'isconnect', 'isdeleted', 'endtime'], 'required'],
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
