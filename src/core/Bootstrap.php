<?php

namespace weikit\core;

use Yii;
use yii\base\Event;
use yii\db\Connection;
use yii\base\BaseObject;
use yii\base\BootstrapInterface;
use weikit\core\addon\ModuleBuilder;

/**
 * 应用初始化设置
 *
 * @package weikit\core
 *
 * @property array $singletons
 */
class Bootstrap extends BaseObject implements BootstrapInterface
{

    /**
     * @var array 默认单例设置
     */
    private $_singletons = [
        'weikit\services\AccountService' => 'weikit\services\AccountService',
        'weikit\services\MenuService' => 'weikit\services\MenuService',
        'weikit\services\ModuleService' => 'weikit\services\ModuleService',
        'weikit\services\ReplyService' => 'weikit\services\ReplyService',
        'weikit\services\WechatAccountService' => 'weikit\services\WechatAccountService',
        'weikit\services\WeikitService' => 'weikit\services\WeikitService',
    ];

    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        $this->registerSingletons();
        $this->mergeClasses();
        $this->checkDbMode();

        $a = new \WeBase;
        $a = \weikit\core\addon\Controller::class;
    }

    /**
     * @return array
     */
    public function getSingletons(): array
    {
        return $this->_singletons;
    }

    /**
     * @param array $singletons
     */
    public function setSingletons(array $singletons)
    {
        $singletons = array_merge($this->_singletons, $singletons);
        $this->_singletons = array_filter($singletons, function($definition) {
            return $definition !== false; // 设置false则取消设置单例
        });
    }

    /**
     * 注册单例类
     */
    public function registerSingletons()
    {
        Yii::$container->setSingletons($this->singletons);
    }

    /**
     * 加载扩展classesMap
     */
    protected function mergeClasses()
    {
        $file = Yii::getAlias('@runtime/classes.php');

        if (!file_exists($file)) { // 构建classes文件缓存

            /* @var ModuleBuilder $moduleBuilder */
            $moduleBuilder = Yii::createObject(ModuleBuilder::class);

            $classes = array_merge(
                [],
                $moduleBuilder->getClasses()
            );

            file_put_contents(Yii::getAlias('@runtime/classes.php'), "<?php\nreturn " . var_export($classes, true) . ';');

        }

        Yii::$classMap = array_merge(Yii::$classMap, require $file);
    }

    /**
     * 兼容历史扩展模块代码
     */
    protected function checkDbMode()
    {
        Event::on(Connection::class, Connection::EVENT_AFTER_OPEN, function ($event) {
            /* @var $connection \yii\db\Connection */
            $connection = $event->sender;
            if ($connection->driverName === 'mysql') {
                // 关闭Mysql sql_mode模式检查
                $connection->pdo->exec("SET SESSION sql_mode='';");
            }
        });
    }
}