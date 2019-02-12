<?php

namespace weikit\core;

use Yii;
use yii\base\ViewRenderer;
use yii\helpers\FileHelper;
use yii\base\ViewNotFoundException;
use yii\base\Controller;
use yii\web\View;

class HtmlViewRenderer extends ViewRenderer
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
     * @return Controller
     */
    public function getContext(): Controller
    {
        return Yii::$app->controller;
    }

    /**
     * @return View
     */
    public function getView(): View
    {
        return $this->getContext()->getView();
    }

    /**
     * @inheritdoc
     */
    public function render($view, $file, $params)
    {
        $cacheFile = $this->getCacheFile($file);
        $this->checkFile($file, $cacheFile);
        return $this->renderPhpFile($cacheFile, $params);
    }

    protected function getCacheFile($file)
    {
        $filename = md5($file);
        return $this->getCachePath() . '/' . substr($filename , 0, 2) . '/' . md5($file) . '.php';
    }

    protected function renderPhpFile($_file_, $_params_ = [])
    {
        $_obInitialLevel_ = ob_get_level();
        ob_start();
        ob_implicit_flush(false);
        extract($GLOBALS, EXTR_SKIP);
        extract($_params_, EXTR_OVERWRITE);
        try {
            require $_file_;
            return ob_get_clean();
        } catch (\Exception $e) {
            while (ob_get_level() > $_obInitialLevel_) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        } catch (\Throwable $e) {
            while (ob_get_level() > $_obInitialLevel_) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }

    protected function checkFile($sourceFile, $targetFile)
    {
        if (!is_file($sourceFile)) {
            throw new ViewNotFoundException("The view file does not exist: $sourceFile");
        }

        if (!is_file($targetFile) || filemtime($sourceFile) > filemtime($targetFile)) {
            $this->compile($sourceFile, $targetFile);
        }
    }

    public function template($filename, $flag = TEMPLATE_DISPLAY) // TODO 更好的统一$this->render
    {
        // TODO 应该通过View::findViewFile来同意返回路径
        $file = $this->getContext()->module->getViewPath() . '/' . $filename . '.' . $this->getView()->defaultExtension;
        $cacheFile = $this->getCacheFile($file);
        $this->checkFile($file, $cacheFile);

        switch ($flag) {
            case TEMPLATE_DISPLAY:
            default:
                extract($GLOBALS, EXTR_SKIP);
                include $this->getCacheFile($cacheFile);
                break;
            case TEMPLATE_FETCH:
                return $this->renderPhpFile($cacheFile);
                break;
            case TEMPLATE_INCLUDEPATH:
                return $cacheFile;
                break;
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