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
                $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB' : null
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
    }

    public function down()
    {
        $this->dropUniTable();
        $this->dropAccountTable();
        $this->dropModuleTable();
    }

    protected function createUniTable()
    {
        $this->createTable('{{%uni_account}}', [
            'uniacid' => $this->primaryKey()->comment('Uniacid'),
            'groupid' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'name' => $this->string(100)->notNull()->defaultValue('')->comment('唯一账户名称'),
            'description' => $this->string()->notNull()->defaultValue('')->comment('唯一账户描述'),
            'default_acid' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'rank' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('排序'),
            'title_initial' => $this->string(1)->notNull()->defaultValue('')->comment('首字母'),
        ], $this->tableOptions);
    }

    protected function dropUniTable()
    {
        $this->dropTable('{{%uni_account}}');
    }

    protected function createAccountTable()
    {
        $this->createTable('{{%account}}', [
            'acid' => $this->primaryKey()->comment('Acid'),
            'uniacid' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Uniacid'),
            'hash' => $this->string(8)->notNull()->defaultValue('')->comment('Hash'),
            'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('账户类型'),
            'isconnect' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('是否激活'),
            'isdeleted' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('已删除'),
            'endtime' => $this->integer(11)->unsigned(),
        ], $this->tableOptions);
        $this->createIndex('idx_uniacid', '{{%account}}', ['uniacid']);

        $this->createTable('{{%account_wechats}}', [
            'acid' => $this->primaryKey()->comment('Acid'),
            'uniacid' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Uniacid'),
            'token' => $this->string(32)->notNull()->defaultValue('')->comment('Token'),
            'encodingaeskey' => $this->string()->notNull()->defaultValue('')->comment('EncodingAESKey'),
            'level' => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('公众号类型'),
            'name' => $this->string(30)->notNull()->defaultValue('')->comment('公众号名称'),
            'account' => $this->string(30)->notNull()->defaultValue('')->comment('公众号账号'),
            'original' => $this->string(50)->notNull()->defaultValue('')->comment('公众号原始ID'),
            'signature' => $this->string(100)->notNull()->defaultValue('')->comment('签名'),
            'key' => $this->string(50)->notNull()->defaultValue('')->comment('Key'),
            'secret' => $this->string(50)->notNull()->defaultValue('')->comment('公众号原始ID'),
            'country' => $this->string(20)->notNull()->defaultValue(''),
            'province' => $this->string(20)->notNull()->defaultValue(''),
            'city' => $this->string(20)->notNull()->defaultValue(''),
            'unsername' => $this->string(30)->notNull()->defaultValue(''),
            'password' => $this->string(32)->notNull()->defaultValue(''),
            'styleid' => $this->integer()->notNull()->defaultValue(''),
            'subscribeurl' => $this->string(120)->notNull()->defaultValue(''),
            'auth_refresh_token' => $this->string()->notNull()->defaultValue(''),
        ], $this->tableOptions);
        $this->createIndex('idx_key', '{{%account_wechats}}', ['key']);
    }

    protected function dropAccountTable()
    {
        $this->dropTable('{{%account}}');
        $this->dropTable('{{%account_wechats}}');
    }

    protected function createModuleTable()
    {
        $this->createTable('{{%modules}}', [
            'id'                   => $this->primaryKey(),
            'username'             => $this->string()->notNull()->unique(),
            'auth_key'             => $this->string(32)->notNull(),
            'password_hash'        => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email'                => $this->string()->notNull()->unique(),

            'status'     => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $this->tableOptions);
    }

    protected function dropModuleTable()
    {
        $this->dropTable('{{%modules}}');
    }
}
