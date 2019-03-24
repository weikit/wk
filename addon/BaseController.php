<?php

namespace weikit\addon;

use Yii;
use yii\web\Controller;
use weikit\services\ModuleService;

/**
 * Class BaseController
 * @package weikit\addon
 * @property boolean $inMobile
 */
abstract class BaseController extends Controller
{
    /**
     * @var ModuleService
     */
    protected $service;
    /**
     * @var intz
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

        if ( ! defined('MODULE_ROOT')) {
            define('MODULE_ROOT', $this->service->getRealPath($this->modulename));
        }
        if ( ! defined('MODULE_URL')) {
            define('MODULE_URL', $this->service->getUrl($this->modulename) . '/');
        }
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
        $view = $this->getView();
        $viewPath = $this->service->getVirtualPath($this->modulename, 'template/' . ($this->inMobile ? 'mobile/' : '') . $filename . '.html');
        $file = $view->viewFile($viewPath);

        if (!is_file($file)) {
            $viewPath = $filename;
        }

        return $view->template($viewPath, TEMPLATE_INCLUDEPATH); // TODO 优化减少viewFile调用次数
    }

    /**
     * @inheritdoc
     */
    public function createAction($id)
    {
        if ($id === '') {
            $id = $this->defaultAction;
        }

        // TODO 更好的兼容, 放在WeikitRule中兼容?
        global $_GPC;
        $_GPC['do'] = $id;
        $_GPC['m'] = $this->modulename;

        $actionMap = $this->actions();
        if (isset($actionMap[$id])) {
            return Yii::createObject($actionMap[$id], [$id, $this]);
        } elseif (preg_match('/^[a-z0-9\\-_]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
            $do = 'do' . ($this->inMobile ? 'Mobile' : 'Web');
            $methodName = $do . str_replace(' ', '', ucwords(str_replace('-', ' ', $id)));
            if (method_exists($this, $methodName)) {
                $method = new \ReflectionMethod($this, $methodName);
                if ($method->isPublic() && $method->getName() === $methodName) {
                    return new DoAction($id, $this, $methodName);
                }
            } else { // 魔术控制器
                return new DoAction($id, $this, $methodName, [
                    'isMagicMethod' => true,
                ]);
            }
        }

        return null;
    }
}