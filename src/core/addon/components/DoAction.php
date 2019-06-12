<?php

namespace weikit\core\addon\components;

use Yii;
use yii\base\InlineAction;

class DoAction extends InlineAction
{
    /**
     * @var bool
     */
    public $isMagicMethod = false;
    /**
     * @inheritdoc
     */
    public function runWithParams($params)
    {
        $args = $this->isMagicMethod ? [] : $this->controller->bindActionParams($this, $params);
        Yii::debug('Running action: ' . get_class($this->controller) . '::' . $this->actionMethod . '()', __METHOD__);
        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }

        // TODO 更好的获取输出内容
        ob_start();
        ob_implicit_flush(false);
        echo call_user_func_array([$this->controller, $this->actionMethod], $args);

        return ob_get_clean();
    }
}