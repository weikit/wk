<?php

namespace weikit\core;

// TODO 暂时静态方式实例化, 待想到更好的方法优化
class We8
{
    private static $_inited = false;

    public static function initWeb()
    {
        if (static::$_inited !== false) {
            return;
        }
        static::$_inited = true;

        static::init();
    }

    public function initApp()
    {
        if (static::$_inited !== false) {
            return;
        }
        static::$_inited = true;

    }

    protected static function init()
    {

    }
}