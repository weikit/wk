<?php

namespace weikit\core\model;

use weikit\core\exceptions\ModelValidationException;

trait ModelTryTrait
{
    /**
     * @param null|array $attributeNames
     * @param bool $clearErrors
     *
     * @return bool
     * @throws ModelValidationException
     */
    public function tryValidate($attributeNames = null, $clearErrors = true)
    {
        if (!$this->validate($attributeNames, $clearErrors)) {
            throw new ModelValidationException($this);
        }

        return true;
    }
}