<?php

namespace weikit\core\addon;

use weikit\services\ModuleService;

/**
 * Class BaseController
 * @package weikit\core\addon
 * @property boolean $inMobile
 * @property Module|\weikit\models\Module $module
 */
class Controller extends \weikit\modules\web\Controller
{
    /**
     * @var string
     */
    public $menu = 'common/menu-account';
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
        // TODO 兼容we8模块路径兼容
//        $this->setViewPath($this->service->basePath . DIRECTORY_SEPARATOR . $this->modulename);

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
        $viewPath = $this->service->getVirtualPath($this->modulename, 'template/' . ($this->inMobile ? 'mobile/' : '') . $filename);
        $file = $view->getTemplateFile($viewPath);

        if (!is_file($file)) { // TODO 做了两次模板路径生成. 优化到一次
            $viewPath = $filename;
        }

        return $view->template($viewPath, TEMPLATE_INCLUDEPATH);
    }
}

class_alias(Controller::class, 'WeBase');