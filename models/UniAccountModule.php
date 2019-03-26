<?php

namespace weikit\models;

use Yii;
use weikit\core\db\ActiveRecord;

/**
 * This is the model class for table "{{%uni_account_modules}}".
 *
 * @property int $id ID
 * @property int $uniacid Uniacid
 * @property string $module 关联模块
 * @property int $enabled 是否开启
 * @property string $settings 设置
 * @property int $shortcut
 * @property int $displayorder 排序
 */
class UniAccountModule extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%uni_account_modules}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uniacid', 'enabled', 'shortcut', 'displayorder'], 'integer'],
            [['settings'], 'string'],
            [['module'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uniacid' => 'Uniacid',
            'module' => '关联模块',
            'enabled' => '是否开启',
            'settings' => '设置',
            'shortcut' => 'Shortcut',
            'displayorder' => '排序',
        ];
    }
}
