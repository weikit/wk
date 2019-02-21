<?php

namespace weikit\core;

class Pinyin extends \Overtrue\Pinyin\Pinyin
{
    /**
     * 获取首词首拼音字母
     * @param string $string
     *
     * @return string
     */
    public function firstChar(string $string)
    {
        $first = mb_substr($string, 0, 1);
        return $this->abbr($first);
    }
}