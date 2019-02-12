<?php

namespace weikit\core;

use weikit\core\view\HtmlViewTrait;

class View extends \yii\web\View
{
    use HtmlViewTrait;

    /**
     * @var string
     */
    public $defaultExtension = 'html';

    /**
     * @inheritdoc
     */
    public function findViewFile($view, $context = null)
    {
        // TODO 需增加theme支持
        $file = parent::findViewFile($view, $context);
        if (pathinfo($file, PATHINFO_EXTENSION) === 'html') { // html 解析
            $cacheFile = $this->getCachePhpFile($file);
            $this->checkCacheFile($file, $cacheFile);
            return $cacheFile;
        }

        return $file;
    }
}