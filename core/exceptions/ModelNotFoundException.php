<?php

namespace weikit\core\exceptions;

use RuntimeException;

class ModelNotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    public $modelClass;
    /**
     * ModelNotFoundException constructor.
     *
     * @param string $className
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($modelClass, $message = '', $code = 0, \Throwable $previous = null)
    {
        $this->modelClass = $modelClass;

        if ($message === '') {
            $message = "No query results for model [{$this->modelClass}]";
        }

        parent::__construct($message, $code, $previous);
    }
}