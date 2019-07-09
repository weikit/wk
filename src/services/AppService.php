<?php

namespace weikit\services;

use Yii;
use weikit\core\Service;
use weikit\core\addon\ModuleBuilder;

class AppService extends Service
{
    /**
     * 构建自定义Yii::$classes
     * TODO 实现interface? 都要实现ClassesBuilder::getClassess
     */
    public function buildClasses()
    {
        /* @var ModuleBuilder $moduleBuilder */
        $moduleBuilder = Yii::createObject(ModuleBuilder::class);

        $classes = array_merge([], $moduleBuilder->getClasses());

        file_put_contents(Yii::getAlias('@runtime/classes.php'), "<?php\nreturn " . var_export($classes, true) . ';');
    }
}