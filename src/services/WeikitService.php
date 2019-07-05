<?php

namespace weikit\services;

use Yii;
use yii\helpers\FileHelper;
use weikit\core\Service;
use weikit\migrations\WeikitMigration;

class WeikitService extends Service
{
    /**
     * @var array
     */
    public $copies = [
        WEIKIT_PATH . '/copy/app' => ABSPATH . 'app',
        WEIKIT_PATH . '/copy/web' => ABSPATH . 'web',
        WEIKIT_PATH . '/copy/api.php' => ABSPATH . 'api.php'
    ];

    /**
     * 启用Weikit
     */
    public function activate()
    {
        foreach($this->copies as $source => $target) {
            if (!file_exists($target)) {
                if (is_dir(($source)) && !@symlink($source, $target)) {
                    FileHelper::copyDirectory($source, $target);
                } elseif (is_file($source) && !@symlink($source, $target)) {
                    FileHelper::createDirectory(dirname($target));
                    @copy($source, $target);
                }
            }
        };

        $this->migrateUp();
    }

    /**
     * @var array
     */
    public $delete = [
        ABSPATH . 'app',
        ABSPATH . 'web',
        ABSPATH . 'api.php'
    ];

    /**
     * 停用Weikit
     *
     * @throws \yii\base\ErrorException
     */
    public function deactivate()
    {
        foreach($this->delete as $target) {
            if (is_dir($target)) {
                FileHelper::removeDirectory($target);
            } elseif (is_file($target)) {
                @unlink($target);
            }

        };
    }

    public function uninstall()
    {
        $this->deactivate();
        $this->migrateDown();
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

    protected function migrateUp()
    {
        $this->beforeContent();

        $this->getMigration()->up();

        return $this->afterContent();
    }

    protected function migrateDown()
    {
        $this->beforeContent();

        $this->getMigration()->down();

        return $this->afterContent();
    }

    protected function beforeContent() {
        ob_start();
        ob_implicit_flush(false);
    }

    protected function afterContent()
    {
        return ob_get_clean();
    }
}