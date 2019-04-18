<?php

namespace weikit\addon;

use weikit\services\ModuleService;
use weikit\core\traits\ControllerMessageTrait;

/**
 * Class BaseController
 * @package weikit\addon
 * @property boolean $inMobile
 * @property Module|\weikit\models\Module $module
 */
class Controller extends \yii\web\Controller
{

    use ControllerMessageTrait;

    /**
     * @var ModuleService
     */
    protected $service;
    /**
     * @var int
     */
    public $uniacid;
    /**
     * @var int
     */
    public $weid;
    /**
     * @var
     */
    public $modulename;

    public function init()
    {
        if ($this->uniacid === null) {
            $this->uniacid = $this->module->uniacid;
        }
        if ($this->weid === null) {
            $this->weid = $this->module->uniacid;
        }
        if ($this->modulename === null) {
            $this->modulename = $this->module->id;
        }
        if ($this->service === null) {
            $this->service = $this->module->service;
        }
        $this->setViewPath($this->service->basePath . DIRECTORY_SEPARATOR . $this->modulename);
    }

    /**
     * @return bool
     */
    public function getInMobile()
    {
        return defined('IN_MOBILE');
    }

    protected function createMobileUrl($do, $query = [], $noredirect = true)
    {
    }

    protected function createWebUrl($do, array $query = [])
    {
        return wurl('site/entry/' . $do, array_merge($query, [
            'm' => $this->modulename
        ]));
    }

    /**
     * @param $filename
     *
     * @return string
     */
    protected function template($filename)
    {
        /** @var $view \weikit\core\View */
        $view = $this->getView();
        $viewPath = $this->service->getVirtualPath($this->modulename, 'template/' . ($this->inMobile ? 'mobile/' : '') . $filename . '.html');
        $file = $view->getTemplateFile($viewPath);

        if (!is_file($file)) {
            $viewPath = $filename;
        }

        return $view->template($viewPath, TEMPLATE_INCLUDEPATH); // TODO 优化减少viewFile调用次数
    }
}