<?php

namespace weikit\models;

use Yii;
use weikit\core\db\ActiveRecord;

/**
 * This is the model class for table "{{%modules_bindings}}".
 *
 * @property int $eid Eid
 * @property string $module 关联模块
 * @property string $entry 功能入口
 * @property string $call
 * @property string $title 功能名称
 * @property string $do
 * @property string $state 状态
 * @property int $direct
 * @property string $url 是否含有设置页
 * @property string $icon 功能图标
 * @property int $displayorder 排序
 */
class ModuleBinding extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%modules_bindings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['module', 'title', 'entry', 'do'], 'required'],
            [['call', 'title', 'icon'], 'string', 'max' => 50],
            [['module', 'url'], 'string', 'max' => 100],
            [['entry'], 'string', 'max' => 30],
            [['do', 'state'], 'string', 'max' => 200],
            [['direct'], 'boolean'],
            [['displayorder'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'eid' => 'Eid',
            'module' => '关联模块',
            'entry' => '功能入口',
            'call' => 'Call',
            'title' => '功能名称',
            'do' => 'Do',
            'state' => '状态',
            'direct' => 'Direct',
            'url' => '是否含有设置页',
            'icon' => '功能图标',
            'displayorder' => '排序',
        ];
    }
}
