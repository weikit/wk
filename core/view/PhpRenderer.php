<?php

namespace weikit\core\view;

use Yii;
use yii\base\ViewRenderer;

class PhpRenderer extends ViewRenderer
{
    /**
     * @param \yii\base\View $view
     * @param string $file
     * @param array $params
     *
     * @return string
     * @throws \Throwable
     */
    public function render($view, $file, $params)
    {
        return $view->renderPhpFile($file, array_merge([
            'app' => Yii::$app,
            'view' => $view,
        ], $params));
    }
}