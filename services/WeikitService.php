<?php

namespace weikit\services;

use weikit\migrations\WeikitMigration;
use Yii;
use yii\helpers\FileHelper;
use weikit\core\service\BaseService;

class WeikitService extends BaseService
{
    /**
     * @var array
     */
    public $copies = [
        WEIKIT_PATH . '/copy/app' => ABSPATH . 'app',

        WEIKIT_PATH . '/copy/web' => ABSPATH . 'web',
    ];

    /**
     * 启用Weikit
     */
    public function activate()
    {
        foreach(static::$copies as $source => $target) {
            if (!symlink($source, $target)) {
                FileHelper::copyDirectory($source, $target);
            }
        };
        $this->getMigration()->up();
    }

    /**
     * @var array
     */
    public $delete = [
        ABSPATH . 'app',
        ABSPATH . 'web',
    ];

    /**
     * 停用Weikit
     *
     * @throws \yii\base\ErrorException
     */
    public function deactivate()
    {
        foreach(static::$delete as $target) {
            FileHelper::removeDirectory($target);
        };
    }

    public function uninstall()
    {
        $this->deactivate();
        $this->getMigration()->down();
    }

    public $migrationClass = 'weikit\migrations\WeikitMigration';

    /**
     * @return WeikitMigration
     * @throws \yii\base\InvalidConfigException
     */
    protected function getMigration()
    {
        return Yii::createObject($this->migrationClass);
    }
}