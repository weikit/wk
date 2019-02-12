<?php
defined('ABSPATH') || exit;

require_once ( __DIR__ . '/defines.php' );
require_once ( __DIR__ . '/vendor/autoload.php' );

if (SHORTINIT) {
    require_once ( ABSPATH . WPINC . '/class-wp-user.php' );
    require_once ( ABSPATH . WPINC . '/class-wp-roles.php' );
    require_once ( ABSPATH . WPINC . '/class-wp-role.php' );
    require_once ( ABSPATH . WPINC . '/class-wp-session-tokens.php' );
    require_once ( ABSPATH . WPINC . '/class-wp-user-meta-session-tokens.php' );
    require_once ( ABSPATH . WPINC . '/formatting.php' );
    require_once ( ABSPATH . WPINC . '/capabilities.php' );
    require_once ( ABSPATH . WPINC . '/user.php' );
    require_once ( ABSPATH . WPINC . '/meta.php' );
    require_once ( ABSPATH . WPINC . '/formatting.php' );
    require_once ( ABSPATH . WPINC . '/link-template.php' );

    wp_plugin_directory_constants();
    wp_cookie_constants();

    require_once ( ABSPATH . WPINC . '/kses.php' );
    require_once ( ABSPATH . WPINC . '/rest-api.php' );
    require_once ( ABSPATH . WPINC . '/pluggable.php' );
}

define('YII_DEBUG', WP_DEBUG);
define('YII_ENV', YII_DEBUG ? 'dev' : 'prod');
define('YII_BEGIN_TIME', $timestart);

(function() {
    require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

    $config = require ( __DIR__ . '/config/web.php' );

    (new weikit\core\Application($config))->run();
})();