<?php

namespace weikit\assets;

use yii\web\AssetBundle;

class IframeResizerContentAsset extends AssetBundle
{
    public $basePath = '@webroot/resource';
    public $baseUrl = '@web/resource';
    public $css = [
    ];
    public $js = [
        'components/iframe-resizer/iframeResizer.contentWindow.js',
    ];
}