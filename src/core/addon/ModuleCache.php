<?php

namespace weikit\core\addon;

use weikit\services\ModuleService;
use yii\base\BaseObject;

class ModuleCache extends BaseObject
{
    /**
     * @var ModuleService
     */
    protected $service;

    public function __construct(ModuleService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($config);
    }

    public function build()
    {
        $modules = $this->service->findAll([]);
    }
}