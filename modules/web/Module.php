<?php

namespace weikit\modules\web;

use yii\base\Behavior;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'weikit\modules\web\controllers';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'we8' => [
                'class' => Behavior::class,
                'events' => [
                    self::EVENT_BEFORE_ACTION => ['\weikit\core\We8', 'initWeb']
                ]
            ]
        ];
    }
}