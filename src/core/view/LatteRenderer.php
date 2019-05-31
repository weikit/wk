<?php

namespace weikit\core\view;

use Yii;
use Latte\Engine;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;
use yii\base\ViewRenderer;

/**
 * // TODO 待开发, 机制兼容问题, 待解决
 * Class LatteRenderer
 * @package weikit\core\view
 * @property MacroSet $macroSet
 */
class LatteRenderer extends ViewRenderer
{
    /**
     * @var MacroSet
     */
    private $_macroSet;
    /**
     * @var string
     */
    public $compilePath = '@runtime/tpl';

    /**
     * @var string
     */
    public $engineClass = '\Latte\Engine';

    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @return MacroSet
     */
    public function getMacroSet(): MacroSet
    {
        if ($this->_macroSet === null) {
            $this->_macroSet = new MacroSet($this->engine->getCompiler());
        }
        return $this->_macroSet;
    }

    /**
     * @param MacroSet $macroSet
     */
    public function setMacroSet(MacroSet $macroSet)
    {
        $this->_macroSet = $macroSet;
    }

    public function init()
    {
        $this->engine = new $this->engineClass();
        $this->engine->setTempDirectory(Yii::getAlias($this->compilePath));
        $this->registerMacros();
    }

    public function render($view, $file, $params)
    {
        return $this->engine->render($file, $params);
    }

    protected function registerMacros()
    {
        $this->getMacroSet()
            ->addMacro('template', [$this, 'macroTemplate'])
            ->addMacro('loop', '', [$this, 'macroLoop'])
            ->addMacro('url', [$this, 'macroUrl']);
    }

    /**
     * {template 'file'}
     * @param $node
     */
    public function macroTemplate(MacroNode $node, PhpWriter $writer)
    {
        return '';
    }

    public function macroLoop(MacroNode $node, PhpWriter $writer)
    {
        $args = preg_split('/\ +/', $node->args);
        $expr = $args[0] . ' as ' . $args[1];
        if (count($args) === 3) {
            $expr .= ' => ' . $args[2];
        }
        $isArray = 'if (isset(' . $args[0] . ') && is_array(' . $args[0] . '))';
        $node->openingCode = '<?php ' . $isArray . ' foreach (' . $expr . ') { ?>';
        $node->closingCode = '<?php } ?>';
    }

    public function macroUrl(MacroNode $node, PhpWriter $writer)
    {
        $args = explode(' ', $node->args, 2);
        $expr = 'url(' . $args[0] . (count($args) === 2 ? ', ' . trim($args[1]) : '' ) . ')';
        $node->openingCode = '<?php ' . $expr . ' ?>';
    }
}