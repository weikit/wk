<?php

namespace weikit\models;

use Yii;
use weikit\core\db\ActiveRecord;

/**
 * This is the model class for table "{{%rule_keyword}}".
 *
 * @property int $id
 * @property int $rid Rid
 * @property int $uniacid Uniacid
 * @property string $module 关联模块
 * @property string $content 回复规则内容
 * @property int $type 类型
 * @property int $status 状态
 * @property int $displayorder 排序
 *
 * @property Rule $rule
 */
class RuleKeyword extends ActiveRecord
{
    /**
     * text类型请求 直接匹配关键字
     */
    const TYPE_MATCH = 1;
    /**
     * text类型请求 包含关键字
     */
    const TYPE_REGULAR = 2;
    /**
     * text类型请求 正则表达式
     */
    const TYPE_INCLUDE = 3;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%rule_keyword}}';
    }

    /**
     * @return RuleKeywordQuery
     */
    public static function find()
    {
        return Yii::createObject(RuleKeywordQuery::class, [get_called_class()]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rid', 'uniacid', 'type', 'status', 'displayorder'], 'integer'],
            [['module'], 'string', 'max' => 100],
            [['content'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rid' => 'Rid',
            'uniacid' => 'Uniacid',
            'module' => '关联模块',
            'content' => '回复规则内容',
            'type' => '类型',
            'status' => '状态',
            'displayorder' => '排序',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRule()
    {
        return $this->hasOne(Rule::class, ['id' => 'rid']);
    }
}
