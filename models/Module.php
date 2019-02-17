<?php

namespace weikit\models;

use Yii;

/**
 * This is the model class for table "{{%ims_modules}}".
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
class Module extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{ims_modules}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type', 'title', 'version', 'ability', 'description', 'author', 'url', 'settings', 'subscribes', 'handles', 'isrulefields', 'issystem', 'target', 'iscard', 'permissions', 'title_initial', 'wxapp_support', 'welcome_support', 'oauth_type', 'webapp_support', 'phoneapp_support', 'account_support', 'xzapp_support'], 'required'],
            [['settings', 'isrulefields', 'issystem', 'target', 'iscard', 'wxapp_support', 'welcome_support', 'oauth_type', 'webapp_support', 'phoneapp_support', 'account_support', 'xzapp_support'], 'integer'],
            [['name', 'title'], 'string', 'max' => 100],
            [['type'], 'string', 'max' => 20],
            [['version'], 'string', 'max' => 15],
            [['ability', 'subscribes', 'handles'], 'string', 'max' => 500],
            [['description'], 'string', 'max' => 1000],
            [['author'], 'string', 'max' => 50],
            [['url'], 'string', 'max' => 255],
            [['permissions'], 'string', 'max' => 5000],
            [['title_initial'], 'string', 'max' => 1],
        ];
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
