<?php

namespace weikit\core\addon;

use Yii;

/**
 * @package weikit\core\addon
 *
 * @property bool $isCoreModule
 */
trait ModuleModelTrait
{
    /**
     * 获取插件Url
     *
     * @param $name
     *
     * @return bool|string
     */
    public function getUrl($name)
    {
        return Yii::getAlias($this->baseUrl . '/' . $name);
    }

    /**
     * 获取扩展模块虚拟路径
     *
     * @param $name
     * @param null $file
     *
     * @return string
     */
    public function getVirtualPath($name, $file = null)
    {
        $basePath = $this->isCoreModule ? $this->basePath : $this->coreBasePath;
        return $basePath . '/' . $name . '/' . $file;
    }

    /**
     * 获取扩展模块真实路径
     *
     * @param $name
     * @param null $file
     *
     * @return bool|string
     */
    public function getRealPath($name, $file = null)
    {
        return Yii::getAlias($this->getVirtualPath($name, $file));
    }

    /**
     * 是否核心模块
     *
     * @return bool
     */
    public function getIsCoreModule()
    {
        return $this->type == 'system';
    }
}