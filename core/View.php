<?php

namespace weikit\core;

use Yii;
use weikit\core\view\HtmlRenderer;
use yii\base\InvalidCallException;

class View extends \yii\web\View
{
    public function template($view, $flag = TEMPLATE_DISPLAY)
    {
        // template默认基础路径从$this->context->module->viewPath开始
        if ( ! in_array(substr($view, 0, 1), ['/', '@'])) {
            $view = '/' . $view;
        }

        $viewFile = $this->findViewFile($view, $this->context);

        switch ($flag) {
            case TEMPLATE_DISPLAY:
            default:
                echo $this->renderFile($viewFile, $GLOBALS);
                break;
            case TEMPLATE_FETCH:
                return $this->renderFile($viewFile, $GLOBALS);
                break;
            case TEMPLATE_INCLUDEPATH:
                $ext = pathinfo($viewFile, PATHINFO_EXTENSION);
                if (isset($this->renderers[$ext])) {
                    if (is_array($this->renderers[$ext]) || is_string($this->renderers[$ext])) {
                        $this->renderers[$ext] = Yii::createObject($this->renderers[$ext]);
                    }
                    /* @var $renderer HtmlRenderer */
                    $renderer = $this->renderers[$ext];
                    if (!$renderer instanceof HtmlRenderer) {
                        throw new InvalidCallException('Only ' . HtmlRenderer::class . ' support return compiled view file path');
                    }
                    $viewFile = $renderer->getCacheFile($viewFile);
                }

                return $viewFile;
                break;
        }
    }
}