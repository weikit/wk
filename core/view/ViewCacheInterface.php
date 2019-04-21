<?php

namespace weikit\core\view;

interface ViewCacheInterface
{
    /**
     * @param string $file
     * @param bool $check
     *
     * @return string
     */
    public function getCacheFile(string $file, bool $check = true): string;
}