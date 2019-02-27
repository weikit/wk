<?php

namespace weikit\models;

use weikit\core\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%uni_account}}".
 *
 * @property int $uniacid
 * @property int $groupid // TODO 字段非空, 待优化移除
 * @property string $name // TODO 字段非空, 待优化移除
 * @property string $description
 * @property int $default_acid
 * @property int $rank
 * @property string $title_initial
 */
class UniAccount extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%uni_account}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['rank'], 'integer'],
            [['title_initial'], 'default',  'value' => function() {
                return $this->defaultTitleInitial();
            }],
            [['title_initial'], 'string', 'max' => 1],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return string
     */
    public function defaultTitleInitial($name = null)
    {
        return strtoupper(Yii::$app->pinyin->firstChar($name ?? $this->name));
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uniacid' => 'Uniacid',
            'groupid' => 'Groupid',
            'name' => 'Name',
            'description' => 'Description',
            'default_acid' => 'Default Acid',
            'rank' => 'Rank',
            'title_initial' => 'Title Initial',
        ];
    }
}
