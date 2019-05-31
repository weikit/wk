<?php

namespace weikit\assets;

use yii\web\AssetBundle;
use yii\web\View;

class IframeResizerContentAsset extends AssetBundle
{
    public $basePath = '@webroot/resource';
    public $baseUrl = '@web/resource';
    public $css = [
    ];
    public $js = [
        'components/iframe-resizer/iframeResizer.contentWindow.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
}