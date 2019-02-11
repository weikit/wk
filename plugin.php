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

require_once ( __DIR__ . '/defines.php' );
require_once ( __DIR__ . '/vendor/autoload.php' );

if (is_admin()) {
    // web目录加入后台cookie中
    add_action('set_auth_cookie', [Plugin::class, 'setAuthCookie'], 10, 6);
    // TODO install uninstall hook?
    // 插件启用
    register_activation_hook(__FILE__, [Plugin::class, 'activate']);
    // 插件禁用
    register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);
    // 注册后台入口菜单
    add_action('admin_menu', function() {
        add_menu_page(
            'Weikit',
            'Weikit',
            'manage_options',
            'weikit',
            function() {
                // 插件地址
                $url = home_url('/web/index.php');
                wp_enqueue_script('iframe-resizer', site_url('web/resource/components/iframe-resizer/iframeResizer.min.js'))
?>
<iframe src="<?= $url ?>" id="wk_iframe" name="wk_iframe" frameborder="0" width="100%" scrolling="no"></iframe>
<script type="text/javascript">
  jQuery(function($) {
    iFrameResize();
  })
</script>
<?php
            },
            'dashicons-screenoptions',
            3.45
        );
    });
}

