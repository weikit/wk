<?php

namespace weikit\core\exceptions;

use yii\base\Model;

class ModelValidationException extends \Exception
{
    /**
     * @var Model
     */
    public $model;

    /**
     * ModelValidationException constructor.
     *
     * @param Model $model
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(Model $model, $message = '', $code = 0, \Throwable $previous = null)
    {
        $this->model = $model;

        if ($message === '') {
            $className = get_class($this->model);
            $message = "Model [{$className}] attributes validate failed: \n" . implode("\n", $this->model->getFirstErrors());
        }

        parent::__construct($message, $code, $previous);
    }
}