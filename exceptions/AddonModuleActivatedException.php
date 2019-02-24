<?php

namespace weikit\exceptions;

use Exception;

class AddonModuleActivatedException extends Exception
{
    /**
     * @var string
     */
    public $moduleName;

    /**
     * AddonModuleActivatedException constructor.
     *
     * @param string $moduleName
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($moduleName, $message = '', $code = 0, \Throwable $previous = null)
    {
        $this->moduleName = $moduleName;

        if ($message === '') {
            $message = "扩展模块[{$moduleName}]已经安装";
        }

        parent::__construct($message, $code, $previous);
    }
}