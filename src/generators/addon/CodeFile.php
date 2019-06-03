<?php
namespace weikit\generators\addon;

use Yii;
use weikit\services\ModuleService;

class CodeFile extends \yii\gii\CodeFile
{
    /**
     * @var ModuleService
     */
    protected $service;

    public function __construct($path, $content, ModuleService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($path, $content, $config);
    }

    public function getRelativePath()
    {
        $basePath = Yii::getAlias($this->service->basePath);
        if (strpos($this->path, $basePath) === 0) {
            return substr($this->path, strlen($basePath) + 1);
        }

        return $this->path;
    }
}