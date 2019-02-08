<?php
/**
 * Plugin Name: wk
 * Plugin URI: https://github.com/weikit/wk
 * Description: Weikit
 * Version: 0.0.1
 * Author: callmez
 * Author URI: https://github.com/callmez
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */
use weikit\core\hooks\Plugin;

defined('ABSPATH') || exit;

if (is_admin()) {
    require __DIR__ . '/vendor/autoload.php';

    // web目录加入后台cookie中
    add_action('set_auth_cookie', [Plugin::class, 'setAuthCookie'], 10, 6);
    // TODO install uninstall hook?
    // 插件启用
    register_activation_hook(__FILE__, [Plugin::class, 'activate']);
    // 插件禁用
    register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);
}

