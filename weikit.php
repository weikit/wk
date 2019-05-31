<?php
defined('ABSPATH') || exit;

define('WEIKIT_FILE', __FILE__);
define('WEIKIT_PATH', __DIR__);

require_once ( __DIR__ . '/vendor/autoload.php' );

add_action('wk_init', function() { // 初始化Weikit
    if (!class_exists('Yii', false)) {
        global $timestart;
        define('YII_DEBUG', WP_DEBUG);
        define('YII_ENV', YII_DEBUG ? 'dev' : 'prod');
        define('YII_BEGIN_TIME', $timestart);

        require_once ( __DIR__ . '/vendor/yiisoft/yii2/Yii.php' );

        // 加载容器单例设置
        Yii::$container->setSingletons( require ( __DIR__ . '/config/singletons.php' ) );

        new weikit\core\Application(
            apply_filters( 'wk_config', require ( __DIR__ . '/config/web.php' ) )
        );
    }
});

if (SHORTINIT) { // Wordpress精简运行模式(SHORTINIT)加载基本wordpress功能保证WeiKit最优性能运行
    add_action('wk_run', function() { // 精简模式直接输出
        Yii::$app->run();
    });

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

    do_action( 'wk_init' );

    do_action( 'wk_run' );
} else { // 正常运行模式将在插件全部加载后运行
    add_action('plugins_loaded', function() {
        do_action('wk_init');
    });
}