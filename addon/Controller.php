<?php

namespace weikit\addon;

use Yii;
use weikit\services\ModuleService;

/**
 * Class BaseController
 * @package weikit\addon
 * @property boolean $inMobile
 */
abstract class Controller extends \yii\web\Controller
{
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
     * @var Module
     */
    public $module;
    /**
     * @var
     */
    public $modulename;

    public function init()
    {
        global $_W;
        if ($this->uniacid === null) {
            $this->uniacid = $_W['uniacid'];
        }
        if ($this->weid === null) {
            $this->weid = $_W['uniacid'];
        }

        $this->modulename = $this->module->id;
        $this->service = Yii::createObject(ModuleService::class);
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