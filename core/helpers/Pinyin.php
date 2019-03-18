<?php

namespace weikit\core\helpers;

use Yii;
use Overtrue\Pinyin\Pinyin as BasePinyin;

class Pinyin
{
    /**
     * @var BasePinyin
     */
    public static $pinyin;

    /**
     * 获取首词首拼音字母
     * @param string $string
     *
     * @return string
     */
    public static function firstChar(string $string)
    {
        return static::instance()->abbr(mb_substr($string, 0, 1));
    }

    /**
     * @return BasePinyin
     */
    protected static function instance()
    {
        if (static::$pinyin === null) {
            static::$pinyin = Yii::createObject(BasePinyin::class);
        }
        return static::$pinyin;
    }

    /**
     * @param $method
     * @param $params
     *
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        return call_user_func_array([static::instance(), $method], $params);
    }
}