<?php

namespace weikit\addon;

use Yii;
use yii\base\InlineAction;

class DoAction extends InlineAction
{
    /**
     * Runs this action with the specified parameters.
     * This method is mainly invoked by the controller.
     * @param array $params action parameters
     * @return mixed the result of the action
     */
    public function runWithParams($params)
    {
        $args = $this->controller->bindActionParams($this, $params);
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