<?php

namespace weikit\models;

use weikit\core\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%uni_account}}".
 *
 * @property int $uniacid
 * @property int $groupid
 * @property string $name
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
            [['groupid', 'name', 'description', 'default_acid', 'title_initial'], 'required'],
            [['groupid', 'default_acid', 'rank'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 255],
            [['title_initial'], 'string', 'max' => 1],
        ];
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
