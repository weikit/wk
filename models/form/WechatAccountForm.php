<?php

namespace weikit\models\form;

use yii\base\Model;
use weikit\models\WechatAccount;
use weikit\core\db\ModelTryTrait;

class WechatAccountForm extends Model
{
    use ModelTryTrait;

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
}