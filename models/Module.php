<?php

namespace weikit\models;

use weikit\core\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%modules}}".
 *
 * @property int $mid
 * @property string $name
 * @property string $type
 * @property string $title
 * @property string $version
 * @property string $ability
 * @property string $description
 * @property string $author
 * @property string $url
 * @property int $settings
 * @property string $subscribes
 * @property string $handles
 * @property int $isrulefields
 * @property int $issystem
 * @property int $target
 * @property int $iscard
 * @property string $permissions
 * @property string $title_initial
 * @property int $wxapp_support
 * @property int $welcome_support
 * @property int $oauth_type
 * @property int $webapp_support
 * @property int $phoneapp_support
 * @property int $account_support
 * @property int $xzapp_support
 */
class Module extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%modules}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    { // TODO 增加模块安装必要验证
        return [
            [['name', 'title', 'type', 'version', 'ability'], 'required'],
            [['name', 'title'], 'string', 'max' => 100],
            [['name'], 'unique'],
            [['target', 'oauth_type'], 'integer'],
            [['settings', 'isrulefields', 'iscard', 'issystem', 'wxapp_support', 'welcome_support', 'webapp_support', 'phoneapp_support', 'account_support', 'xzapp_support'], 'boolean'],
            [['type'], 'string', 'max' => 20],
            [['version'], 'string', 'max' => 15],
            [['ability'], 'string', 'max' => 500],
            [['description'], 'string', 'max' => 1000],
            [['author'], 'string', 'max' => 50],
            [['url'], 'string', 'max' => 255],
            [['title_initial'], 'default',  'value' => function() {
                return $this->defaultTitleInitial();
            }],
            [['title_initial'], 'string', 'max' => 1],
            [['subscribes', 'handles', 'permissions'], 'safe'],
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
            'mid' => 'Mid',
            'name' => 'Name',
            'type' => 'Type',
            'title' => 'Title',
            'version' => 'Version',
            'ability' => 'Ability',
            'description' => 'Description',
            'author' => 'Author',
            'url' => 'Url',
            'settings' => 'Settings',
            'subscribes' => 'Subscribes',
            'handles' => 'Handles',
            'isrulefields' => 'Isrulefields',
            'issystem' => 'Issystem',
            'target' => 'Target',
            'iscard' => 'Iscard',
            'permissions' => 'Permissions',
            'title_initial' => 'Title Initial',
            'wxapp_support' => 'Wxapp Support',
            'welcome_support' => 'Welcome Support',
            'oauth_type' => 'Oauth Type',
            'webapp_support' => 'Webapp Support',
            'phoneapp_support' => 'Phoneapp Support',
            'account_support' => 'Account Support',
            'xzapp_support' => 'Xzapp Support',
        ];
    }
}
