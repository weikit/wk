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

    public static function activate()
    {
        $dirs = ['web', 'app'];

        array_walk($dirs, function($name) {
            $path = ABSPATH . $name;
            FileHelper::createDirectory($path);

            $file = $path . '/index.php';
            $content = <<<EOF
<?php
define('SHORTINIT', true);

require '../wp-load.php';
require '../wp-content/plugins/wk/init.php';
EOF;

            if (($fp = @fopen($file, 'w')) === false) {
                throw new InvalidConfigException("Unable to append to log file: {$file}");
            }
            @fwrite($fp, $content);
            @fclose($fp);
        });
    }

    public static function deactivate()
    {
        $dirs = ['web', 'app'];
        array_walk($dirs, function($name) {
            $path = ABSPATH . $name;
            FileHelper::removeDirectory($path);
        });
    }
}