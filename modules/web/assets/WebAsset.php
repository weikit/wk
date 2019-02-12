<?php

namespace weikit\modules\web\assets;

use yii\web\AssetBundle;
use yii\web\View;

class WebAsset extends AssetBundle
{
    public $basePath = '@wp_path/web/resource';
    public $baseUrl = '@wp/web/resource';
    public $css = [
        'css/common.css',
    ];
    public $js = [
        'js/app/util.js',
        'js/app/common.js',
        'js/require.js'
    ];
    public $depends = [
        'weikit\assets\IframeResizerContentAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
}