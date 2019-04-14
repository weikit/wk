<?php

namespace weikit\migrations;

use yii\db\Migration;

/**
 * Class WeikitMigration
 * @package weikit\migrations
 * @property string $tableOptions
 */
class WeikitMigration extends Migration
{
    /**
     * @var string|null
     */
    private $_tableOptions;

    /**
     * @return string|null
     */
    public function getTableOptions()
    {
        if ($this->_tableOptions === false) {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $this->setTableOptions(
                $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null
            );
        }

        return $this->_tableOptions;
    }

    /**
     * @param string|null $tableOptions
     */
    public function setTableOptions(string $tableOptions): void
    {
        $this->_tableOptions = $tableOptions;
    }

    public function up()
    {
        $this->createUniTable();
        $this->createAccountTable();
        $this->createModuleTable();
        $this->createReplyRuleTable();
    }

    /**
     * @return bool|false|string
     */
    public function down()
    {
        $this->dropUniTable();
        $this->dropAccountTable();
        $this->dropModuleTable();
        $this->drupReplyRuleTable();
    }

    protected function createUniTable()
    {
        if ( ! $this->isTableExists('{{%uni_account}}')) {
            $this->createTable('{{%uni_account}}', [
                'uniacid'       => $this->primaryKey()->comment('Uniacid'),
                'groupid'       => $this->integer()->unsigned()->notNull()->defaultValue(0),
                'name'          => $this->string(100)->notNull()->defaultValue('')->comment('唯一账户名称'),
                'description'   => $this->string()->notNull()->defaultValue('')->comment('唯一账户描述'),
                'default_acid'  => $this->integer()->unsigned()->notNull()->defaultValue(0),
                'rank'          => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('排序'),
                'title_initial' => $this->string(1)->notNull()->defaultValue('')->comment('首字母'),
            ], $this->tableOptions);
        }

        if (! $this->isTableExists('{{%uni_account_modules}}')) {
            $this->createTable('{{%uni_account_modules}}', [
                'id' => $this->primaryKey()->comment('ID'),
                'uniacid'   => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Uniacid'),
                'module' => $this->string(100)->notNull()->defaultValue('')->comment('关联模块'),
                'enabled' => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('是否开启'),
                'settings' => $this->text()->comment('设置'),
                'shortcut' => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment(''),
                'displayorder' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('排序'),
            ]);
        }
    }

    protected function dropUniTable()
    {
        if ($this->isTableExists('{{%uni_account}}')) {
            $this->dropTable('{{%uni_account}}');
        }
        if ($this->isTableExists('{{%uni_account_modules}}')) {
            $this->dropTable('{{%uni_account_modules}}');
        }
    }

    protected function createAccountTable()
    {
        if ( ! $this->isTableExists('{{%account}}')) {
            $this->createTable('{{%account}}', [
                'acid'      => $this->primaryKey()->comment('Acid'),
                'uniacid'   => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Uniacid'),
                'hash'      => $this->string(8)->notNull()->defaultValue('')->comment('Hash'),
                'type'      => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('账户类型'),
                'isconnect' => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('是否激活'),
                'isdeleted' => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('已删除'),
                'endtime'   => $this->integer(11)->unsigned(),
            ], $this->tableOptions);
            $this->createIndex('idx_uniacid', '{{%account}}', ['uniacid']);
        }

        if ( ! $this->isTableExists('{{%account_wechats}}')) {
            $this->createTable('{{%account_wechats}}', [
                'acid'               => $this->primaryKey()->comment('Acid'),
                'uniacid'            => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Uniacid'),
                'token'              => $this->string(32)->notNull()->defaultValue('')->comment('Token'),
                'encodingaeskey'     => $this->string()->notNull()->defaultValue('')->comment('EncodingAESKey'),
                'level'              => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('公众号类型'),
                'name'               => $this->string(30)->notNull()->defaultValue('')->comment('公众号名称'),
                'account'            => $this->string(30)->notNull()->defaultValue('')->comment('公众号账号'),
                'original'           => $this->string(50)->notNull()->defaultValue('')->comment('公众号原始ID'),
                'signature'          => $this->string(100)->notNull()->defaultValue('')->comment('签名'),
                'key'                => $this->string(50)->notNull()->defaultValue('')->comment('Key'),
                'secret'             => $this->string(50)->notNull()->defaultValue('')->comment('公众号原始ID'),
                'country'            => $this->string(20)->notNull()->defaultValue(''),
                'province'           => $this->string(20)->notNull()->defaultValue(''),
                'city'               => $this->string(20)->notNull()->defaultValue(''),
                'username'           => $this->string(30)->notNull()->defaultValue(''),
                'password'           => $this->string(32)->notNull()->defaultValue(''),
                'styleid'            => $this->integer()->notNull()->defaultValue(0),
                'subscribeurl'       => $this->string(120)->notNull()->defaultValue(''),
                'auth_refresh_token' => $this->string()->notNull()->defaultValue(''),
                'lastupdate'         => $this->integer()->notNull()->defaultValue(0),
            ], $this->tableOptions);
            $this->createIndex('idx_key', '{{%account_wechats}}', ['key']);
        }
    }

    protected function dropAccountTable()
    {
        if ($this->isTableExists('{{%account}}')) {
            $this->dropTable('{{%account}}');
        }

        if ( ! $this->isTableExists('{{%account_wechats}}')) {
            $this->dropTable('{{%account_wechats}}');
        }
    }

    protected function createModuleTable()
    {
        if ( ! $this->isTableExists('{{%modules}}')) {
            $this->createTable('{{%modules}}', [
                'mid'                  => $this->primaryKey()->comment('Mid'),
                'name'                 => $this->string(100)->notNull()->defaultValue('')->comment('模块标识'),
                'title'                => $this->string(100)->notNull()->defaultValue('')->comment('模块名称'),
                'type'                 => $this->string(20)->notNull()->defaultValue('')->comment('模块类型'),
                'version'              => $this->string(15)->notNull()->defaultValue('')->comment('版本'),
                'ability'              => $this->string(500)->notNull()->defaultValue('')->comment('简述'),
                'description'          => $this->string(1000)->notNull()->defaultValue('')->comment('模块描述'),
                'author'               => $this->string(50)->notNull()->defaultValue('')->comment('作者'),
                'url'                  => $this->string()->notNull()->defaultValue('')->comment('模块链接'),
                'settings'             => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('是否含有设置页'),
                'subscribes'           => $this->json()->comment('订阅项'),
                'handles'              => $this->json()->comment('事件订阅项'),
                'isrulefields'         => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('是都关键字规则订阅项'),
                'issystem'             => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('是否系统模块'),
                'target'               => $this->string(10)->notNull()->defaultValue(''),
                'iscard'               => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(0),
                'permissions'          => $this->json()->comment('权限'),
                'title_initial'        => $this->string(1)->notNull()->defaultValue('')->comment('模块首字母'),
                'oauth_type'           => $this->boolean()->unsigned()->notNull()->defaultValue(0),
                'oauth_typeoauth_type' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
                'wxapp_support'        => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('支持微信小程序'),
                'welcome_support'      => $this->boolean()->unsigned()->notNull()->defaultValue(0),
                'webapp_support'       => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('支持网页应用'),
                'phoneapp_support'     => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('支持手机App'),
                'account_support'      => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment(''),
                'xzapp_support'        => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('支持熊掌号'),
            ], $this->tableOptions);
            $this->createIndex('idx_name', '{{%modules}}', ['name']);
        }

        if ( ! $this->isTableExists('{{%modules_bindings}}')) {
            $this->createTable('{{%modules_bindings}}', [
                'eid'          => $this->primaryKey()->comment('Eid'),
                'module'       => $this->string(100)->notNull()->defaultValue('')->comment('关联模块'),
                'entry'        => $this->string(30)->notNull()->defaultValue('')->comment('功能入口'),
                'call'         => $this->string(50)->notNull()->defaultValue(''),
                'title'        => $this->string(50)->notNull()->defaultValue('')->comment('功能名称'),
                'do'           => $this->string(200)->notNull()->defaultValue(''),
                'state'        => $this->string(200)->notNull()->defaultValue('')->comment('状态'),
                'direct'       => $this->integer()->unsigned()->notNull()->defaultValue(0),
                'url'          => $this->string(100)->unsigned()->notNull()->defaultValue('')->comment('是否含有设置页'),
                'icon'         => $this->string(50)->notNull()->defaultValue('')->comment('功能图标'),
                'displayorder' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('排序'),
            ], $this->tableOptions);
            $this->createIndex('idx_module', '{{%modules_bindings}}', ['module']);
        }
    }

    protected function dropModuleTable()
    {
        if ($this->isTableExists('{{%modules}}')) {
            $this->dropTable('{{%modules}}');
        }
    }

    protected function createReplyRuleTable()
    {
        if ( ! $this->isTableExists('{{%rule}}')) {
            $this->createTable('{{%rule}}', [
                'id'          => $this->primaryKey()->comment('Rid'),
                'uniacid'            => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Uniacid'),
                'name'        => $this->string(50)->notNull()->defaultValue('')->comment('回复规则名'),
                'module' => $this->string(100)->notNull()->defaultValue('')->comment('关联模块'),
                'status'      => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('状态'),
                'displayorder' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(0)->comment('排序'),
            ], $this->tableOptions);
        }

        if ( ! $this->isTableExists('{{%rule_keyword}}')) {
            $this->createTable('{{%rule_keyword}}', [
                'id'          => $this->primaryKey()->comment(''),
                'rid'            => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Rid'),
                'uniacid'            => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Uniacid'),
                'module'       => $this->string(100)->notNull()->defaultValue('')->comment('关联模块'),
                'content'       => $this->string()->notNull()->defaultValue('')->comment('回复规则内容'),
                'type'      => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('类型'),
                'status'      => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('状态'),
                'displayorder' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(0)->comment('排序'),
            ], $this->tableOptions);
            $this->createIndex('idx_module', '{{%modules_bindings}}', ['module']);
        }
    }

    protected function dropReplyRuleTable()
    {
        if ($this->isTableExists('{{%rule}}')) {
            $this->dropTable('{{%rule}}');
        }
        if ($this->isTableExists('{{%rule_keyword}}')) {
            $this->dropTable('{{%rule_keyword}}');
        }
    }

    /**
     * @param $tablename
     *
     * @return bool
     */
    public function isTableExists($tablename)
    {
        return $this->db->schema->getTableSchema($tablename, true) !== null;
    }
}
