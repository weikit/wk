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
        foreach($this->copies as $source => $target) {
            if (!is_dir(($target)) && !@symlink($source, $target)) {
                FileHelper::copyDirectory($source, $target);
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
    ];

    /**
     * 停用Weikit
     *
     * @throws \yii\base\ErrorException
     */
    public function deactivate()
    {
        foreach($this->delete as $target) {
            FileHelper::removeDirectory($target);
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