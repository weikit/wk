<?php

namespace weikit\core\view;

use Yii;
use yii\helpers\FileHelper;
use yii\base\ViewNotFoundException;

trait HtmlViewTrait
{
    /**
     * @var string
     */
    private $_cachePath;

    /**
     * @return string
     */
    public function getCachePath(): string
    {
        if ($this->_cachePath === null) {
            $this->setCachePath(Yii::getAlias('@runtime/tpl'));
        }
        return $this->_cachePath;
    }

    /**
     * @param string $cachePath
     */
    public function setCachePath(string $cachePath)
    {
        $this->_cachePath = $cachePath;
    }

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

    public function getCachePhpFile($file)
    {
        $filename = md5($file);
        return $this->getCachePath() . '/' . substr($filename , 0, 2) . '/' . md5($file) . '.php';
    }

    protected function checkCacheFile($file, $cacheFile)
    {
        if (!is_file($file)) {
            throw new ViewNotFoundException("The view file does not exist: $file");
        }

        if (!is_file($cacheFile) || filemtime($file) > filemtime($cacheFile)) {
            $this->compile($file, $cacheFile);
        }
    }

    public function compile($source, $target, $inModule = false)
    {
        FileHelper::createDirectory(dirname($target));
        $content = $this->parse(file_get_contents($source), $inModule);
        file_put_contents($target, $content);
    }

    public function parse($str, $inModule = false) {
        $str = preg_replace('/<!--{(.+?)}-->/s', '{$1}', $str);
        $str = preg_replace('/{template\s+(.+?)}/', // todo template 和 this->tempalte
            '<?php (!empty($this) && $this instanceof WeModuleSite || ' . intval($inModule) . ') ? (include $this->template($1, TEMPLATE_INCLUDEPATH)) : (include $this->template($1, TEMPLATE_INCLUDEPATH));?>' . "\n",
            $str);
        $str = preg_replace('/{php(\s([^{}]|{([^{}]*(?2))*})*)}/suU', '<?php$1?>', $str);
        $str = preg_replace('/{if\s+(.+?)}/', '<?php if($1) { ?>', $str);
        $str = preg_replace('/{else}/', '<?php } else { ?>', $str);
        $str = preg_replace('/{else ?if\s+(.+?)}/', '<?php } else if($1) { ?>', $str);
        $str = preg_replace('/{\/if}/', '<?php } ?>', $str);
        $str = preg_replace('/{loop\s+(\S+)\s+(\S+)}/', '<?php if(is_array($1)) { foreach($1 as $2) { ?>', $str);
        $str = preg_replace('/{loop\s+(\S+)\s+(\S+)\s+(\S+)}/', '<?php if(is_array($1)) { foreach($1 as $2 => $3) { ?>', $str);
        $str = preg_replace('/{\/loop}/', '<?php } } ?>', $str);
        $str = preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}/', '<?php echo $1;?>', $str);
        $str = preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\[\]\'\"\$]*)}/', '<?php echo $1;?>', $str);
        $str = preg_replace('/{url\s+(\S+)}/', '<?php echo url($1);?>', $str);
        $str = preg_replace('/{url\s+(\S+)\s+(array\(.+?\))}/', '<?php echo url($1, $2);?>', $str);
        $str = preg_replace('/{media\s+(\S+)}/', '<?php echo tomedia($1);?>', $str);
        $str = preg_replace_callback('/<\?php([^\?]+)\?>/s', [ $this, 'templateAddQuote' ], $str);
        $str = preg_replace('/{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)}/s', '<?php echo $1;?>', $str);
        $str = str_replace('{##', '{', $str);
        $str = str_replace('##}', '}', $str);
        $str = "<?php defined('ABSPATH') || exit;?>\n" . $str;

        return $str;

    }

    public function templateAddQuote($matches)
    {
        $code = "<?php {$matches[1]}?>";
        $code = preg_replace('/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\](?![a-zA-Z0-9_\-\.\x7f-\xff\[\]]*[\'"])/s', "['$1']", $code);

        return str_replace('\\\"', '\"', $code);
    }
}