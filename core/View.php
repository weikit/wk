<?php

namespace weikit\core;

use weikit\core\view\HtmlViewTrait;

class View extends \yii\web\View
{
//    use HtmlViewTrait;
//
//    /**
//     * @var string
//     */
//    public $defaultExtension = 'html';
//
//    /**
//     * @inheritdoc
//     */
//    public function findViewFile($view, $context = null)
//    {
//        // TODO 需增加theme支持
//        $file = parent::findViewFile($view, $context);
//        if (pathinfo($file, PATHINFO_EXTENSION) === 'html') { // html 解析
//            $cacheFile = $this->getCachePhpFile($file);
//            $this->checkCacheFile($file, $cacheFile);
//            return $cacheFile;
//        }
//
//        return $file;
//    }

    public function template($view, $flag = TEMPLATE_DISPLAY)
    {
        // template默认基础路径从$this->context->module->viewPath开始
        if (!in_array(substr($view, 0, 1), ['/', '@'])) {
            $view = '/' . $view;
        }
        $file = $this->findViewFile($view, $this->context);

        switch ($flag) {
            case TEMPLATE_DISPLAY:
            default:
                extract($GLOBALS, EXTR_SKIP);
                include $file;
                break;
            case TEMPLATE_FETCH:
                return $this->renderPhpFile($file);
                break;
            case TEMPLATE_INCLUDEPATH:

                return $file;
                break;
        }
    }
}