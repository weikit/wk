<?php

namespace weikit\core\addon;

use weikit\services\ModuleService;
use Yii;
use yii\web\View;
use yii\base\BaseObject;
use yii\base\ViewContextInterface;
use weikit\models\Module;

/**
 * @package weikit\core\addon
 * @property string $moduleName
 * @property boolean $inMobile
 * @property View $view
 */
abstract class Base extends BaseObject implements ViewContextInterface
{
    private $_view;
    private $_viewPath;

    /**
     * @var int
     */
    public $uniacid;
    /**
     * @var int
     */
    public $weid;
    /**
     * @var Module
     */
    public $module;

    public function init()
    {
        global $_W;
        if ($this->uniacid === null) {
            $this->uniacid = $_W['uniacid'];
        }
        if ($this->weid === null) {
            $this->weid = $_W['uniacid'];
        }
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->module->name;
    }

    /**
     * @return bool
     */
    public function getInMobile()
    {
        return defined('IN_MOBILE');
    }

    protected function template($filename)
    {
        $service = Yii::createObject(ModuleService::class);
        $view = $service->getVirtualPath($this->moduleName, 'template/' . ($this->inMobile ? 'mobile' : '') . '/' . $filename . '.html');
        return $this->view->template($view, TEMPLATE_INCLUDEPATH);
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->setView(Yii::$app->getView());
        }
        return $this->_view;
    }

    /**
     * @param mixed $view
     */
    public function setView(View $view)
    {
        $this->_view = $view;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $moduleService = Yii::createObject(ModuleService::class);
            $this->_viewPath = $moduleService->basePath . DIRECTORY_SEPARATOR . $this->moduleName;
        }

        return $this->_viewPath;
    }

    /**
     * @param $path
     */
    public function setViewPath($path)
    {
        $this->_viewPath = Yii::getAlias($path);
    }
}