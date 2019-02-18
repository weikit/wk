<?php

namespace weikit\models;

use Yii;

/**
 * This is the model class for table "{{ims_account}}".
 *
 * @property int $acid
 * @property int $uniacid
 * @property string $hash
 * @property int $type
 * @property int $isconnect
 * @property int $isdeleted
 * @property int $endtime
 */
class Account extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{ims_account}}';
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
}
