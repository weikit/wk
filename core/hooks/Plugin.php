<?php

namespace weikit\core\hooks;

use yii\helpers\FileHelper;
use yii\base\InvalidConfigException;

class Plugin
{
    public static function setAuthCookie($auth_cookie, $expire, $expiration, $user_id, $scheme)
    {
        $secure = apply_filters( 'secure_auth_cookie', is_ssl(), $user_id );
        $auth_cookie_name = $secure ? SECURE_AUTH_COOKIE : AUTH_COOKIE;
        setcookie($auth_cookie_name, $auth_cookie, $expire, SITECOOKIEPATH . 'web', COOKIE_DOMAIN, $secure, true);
    }

    public static $copies = [
        WEIKIT_PATH . '/copy/app' => ABSPATH . 'app',

        WEIKIT_PATH . '/copy/web' => ABSPATH . 'web',
    ];

    public static function activate()
    {
        foreach(static::$copies as $source => $target) {
            @symlink($source, $target); // TODO 支持软连接和拷贝
//            if (is_file($source)) {
//                FileHelper::createDirectory(dirname($target));
//                if (!file_exists($target)) {
//                    copy($source, $target);
//                }
//            } else {
//                FileHelper::copyDirectory($source, $target);
//            }
        };
    }

    public static $delete = [
        ABSPATH . 'app',
        ABSPATH . 'web',
    ];

    public static function deactivate()
    {
        foreach(static::$delete as $target) {
            if (is_file($target)) {
                FileHelper::unlink($target);
            } else {
                FileHelper::removeDirectory($target);
            }
        };
    }
}