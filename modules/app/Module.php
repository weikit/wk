<?php

namespace weikit\modules\app;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'weikit\modules\app\controllers';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'we8' => [
                'class' => Behavior::class,
                'events' => [
                    self::EVENT_BEFORE_ACTION => ['\weikit\core\We8', 'initApp']
                ]
            ]
        ];
    }
}