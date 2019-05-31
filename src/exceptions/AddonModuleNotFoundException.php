<?php

namespace weikit\exceptions;

use Exception;

class AddonModuleNotFoundException extends Exception
{
    /**
     * @var string
     */
    public $moduleName;

    /**
     * AddonNotFoundException constructor.
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
            $message = "未找到扩展模块[{$moduleName}]或者格式错误";
        }

        parent::__construct($message, $code, $previous);
    }
}