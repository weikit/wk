<?php

namespace weikit\models;

use Yii;
use weikit\core\db\ActiveRecord;

/**
 * This is the model class for table "{{%rule}}".
 *
 * @property int $id Rid
 * @property int $uniacid Uniacid
 * @property string $name 回复规则名
 * @property string $module 关联模块
 * @property int $status 状态
 * @property int $displayorder 排序
 *
 * @property RuleKeyword[] $keywords
 */
class Rule extends ActiveRecord
{
    /**
     * 激活状态
     */
    const STATUS_ACTIVE = 1;
    /**
     * 禁用状态
     */
    const STATUS_DISABLED = 0;
    /**
     * @var array
     */
    public static $statuses = [
        self::STATUS_ACTIVE => '启用',
        self::STATUS_DISABLED => '关闭'
    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%rule}}';
    }

    /**
     * @return RuleQuery
     */
    public static function find()
    {
        return Yii::createObject(RuleQuery::class, [get_called_class()]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uniacid', 'status', 'displayorder'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['module'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Rid',
            'uniacid' => 'Uniacid',
            'name' => '规则名称',
            'module' => '关联模块',
            'status' => '状态',
            'displayorder' => '回复优先级',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(RuleKeyword::class, ['rid' => 'id']);
    }
}
