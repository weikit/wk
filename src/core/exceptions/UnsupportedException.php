<?php

namespace weikit\core\exceptions;

class UnsupportedException extends \RuntimeException
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'This future is unsupported';
    }
}