<?php

namespace weikit\core;

use Yii;
use yii\base\ViewRenderer;
use yii\helpers\FileHelper;

class HtmlViewRenderer extends ViewRenderer
{
    /**
     * @var string
     */
    private $_cachePath;

    /**
     * @return string
     */
    public function getCachePath()
    {
        if ($this->_cachePath === null) {
            $this->setCachePath(Yii::getAlias('@runtime/tpl'));
        }
        return $this->cachePath;
    }

    /**
     * @param string $cachePath
     */
    public function setCachePath(string $cachePath)
    {
        $this->cachePath = $cachePath;
    }

    /**
     * @inheritdoc
     */
    public function render($view, $file, $params)
    {
        $filename = md5($file);
        $cacheFile = $this->getCachePath() . '/' . substr($filename , 0, 2) . '/' . md5($file) . '.php';
        if (!is_file($cacheFile) || filemtime($file) > filemtime($cacheFile)) {
            $this->compile($file, $cacheFile);
        }

        return $view->renderPhpFile($cacheFile, $params);
    }

    public function template($filename, $flag = TEMPLATE_DISPLAY)
    {
        // todo
    }

    public function compile($source, $target, $inModule = false)
    {
        FileHelper::createDirectory(dirname($target));
        $content = $this->parse(file_get_contents($source), $inModule);
        file_put_contents($target, $content);
    }

    public function parse($str, $inModule = false) {
        $str = preg_replace('/<!--{(.+?)}-->/s', '{$1}', $str);
        $str = preg_replace('/{template\s+(.+?)}/',
            '<?php (!empty($this) && $this instanceof WeModuleSite || ' . intval($inModule) . ') ? (include $this->template($1, TEMPLATE_INCLUDEPATH)) : (include template($1, TEMPLATE_INCLUDEPATH));?>' . "\n",
            $str);
        $str = preg_replace('/{php\s+(.+?)}/', '<?php $1?>', $str);
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
        $str = preg_replace_callback('/{hook\s+(.+?)}/s', [ $this, 'templateModuleHookParser' ], $str);
        $str = preg_replace('/{\/hook}/', '<?php ; ?>', $str);
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

    public function templateModuleHookParser($params = [])
    {
        return ''; // TODO
    }
}