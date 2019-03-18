<?php

namespace weikit\core\helpers;

use Detection\MobileDetect;

class Detector
{
    public static $detect;

    /**
     * @return MobileDetect
     */
    protected static function instance()
    {
        if (static::$detect === null) {
            static::$detect = Yii::createObject(MobileDetect::class);
        }
        return static::$detect;
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