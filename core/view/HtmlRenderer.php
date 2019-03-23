<?php

namespace weikit\core\view;

use Yii;
use yii\base\ViewRenderer;
use yii\helpers\FileHelper;
use yii\base\ViewNotFoundException;
use weikit\core\View;

class HtmlRenderer extends ViewRenderer
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
        return $view->renderPhpFile($this->getCacheFile($file), $params);
    }

    /**
     * @param string $file
     * @param bool $check
     *
     * @return string
     */
    public function getCacheFile(string $file, bool $check = true): string
    {
        $md5 = md5($file);
        $filename = substr($md5,0,8);
        if (preg_match('#([/\\\\][\w@.-]{3,35}){1,4}\z#', $file, $matches)) {
            $cacheFile = $this->getCachePath() . $matches[0] . '.' . $filename .'.php';
        } else {
            $cacheFile = $this->getCachePath() . '/' . $filename . '/' . $md5 . '.php';
        }

        if ($check === true && $this->isExpired($file, $cacheFile)) {
            $this->compile($file, $cacheFile);
        }
        return $cacheFile;
    }

    protected function isExpired(string $file, string $cacheFile): bool
    {
        if (!is_file($file)) {
            throw new ViewNotFoundException("The view file does not exist: $file");
        }

        return !is_file($cacheFile) || @filemtime($file) > @filemtime($cacheFile);
    }

    public function compile(string $source, string $target)
    {
        FileHelper::createDirectory(dirname($target));
        $content = $this->parse(file_get_contents($source));
        file_put_contents($target, $content);
    }

    public function parse($str) {
        $str = preg_replace('/<!--{(.+?)}-->/s', '{$1}', $str);
        $str = preg_replace('/{template\s+(.+?)}/', '<?php include $this->template($1, TEMPLATE_INCLUDEPATH) ?>', $str);

        $str = preg_replace('/{encode\s+(.+?)}/', '<?php echo \yii\helpers\Html::encode($1) ?>', $str);
        $str = preg_replace('/{to\s+(.+?)}/', '<?php echo \yii\helpers\Url::to($1) ?>', $str);

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
        $str = preg_replace('/{url\s+(\S+)\s+(\[.+?\])}/', '<?php echo url($1, $2);?>', $str);

        $str = preg_replace('/{media\s+(\S+)}/', '<?php echo tomedia($1);?>', $str);
        $str = preg_replace_callback('/<\?php([^\?]+)\?>/s', [ $this, 'templateAddQuote' ], $str);
        $str = preg_replace('/{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)}/s', '<?php echo $1;?>', $str);

        $str = str_replace('{##', '{', $str);
        $str = str_replace('##}', '}', $str);

        $str = "<?php defined('ABSPATH') || exit;?>\n" . $str;

        // TODO 减少输出内容体积
//        return trim(preg_replace('/>\s+</', '><', $str));

        return $str;
    }

    public function templateAddQuote($matches)
    {
        $str = '<?php ' . trim($matches[1]) . ' ?>';
        $str = preg_replace('/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\](?![a-zA-Z0-9_\-\.\x7f-\xff\[\]]*[\'"])/s', "['$1']", $str);

        return str_replace('\\\"', '\"', $str);
    }
}