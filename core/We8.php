<?php

namespace weikit\core {

    use Yii;

    // TODO 暂时静态方式实例化, 待想到更好的方法优化
    class We8
    {
        /**
         * @var We8
         */
        private static $_instance;

        public static function initWeb()
        {
            if (static::$_instance !== null) {
                return;
            }

            static::$_instance = new static();
        }

        public static function initApp()
        {
            if (static::$_instance !== null) {
                return;
            }

            static::$_instance = new static();
        }

        private function __construct()
        {
            $this->initConstants();
            require_once WE8_PATH . '/framework/class/loader.class.php'; // TODO 改进机制
            $this->init();
        }

        public function initConstants()
        {
            // base
            define('IN_IA', true);
            define('TIMESTAMP', intval(YII_BEGIN_TIME));
            define('STARTTIME', (YII_BEGIN_TIME - TIMESTAMP) . '0000 ' . TIMESTAMP);
            define('IA_ROOT', Yii::getAlias('@wp'));
            define('WE8_PATH', Yii::getAlias('@weikit/we8'));

            define('DEVELOPMENT', YII_DEBUG);
            define('ATTACHMENT_ROOT', wp_get_upload_dir()['basedir']);

            // version
            define('IMS_FAMILY', 'common');
            define('IMS_VERSION', '0.0.1');
            define('IMS_RELEASE_DATE', '201901010001');

            // constants
            define('TEMPLATE_DISPLAY', 0);
            define('TEMPLATE_FETCH', 1);
            define('TEMPLATE_INCLUDEPATH', 2);

            define('ACCOUNT_SUBSCRIPTION', 1);
            define('ACCOUNT_SUBSCRIPTION_VERIFY', 3);
            define('ACCOUNT_SERVICE', 2);
            define('ACCOUNT_SERVICE_VERIFY', 4);
            define('ACCOUNT_TYPE_OFFCIAL_NORMAL', 1);
            define('ACCOUNT_TYPE_OFFCIAL_AUTH', 3);
            define('ACCOUNT_TYPE_APP_NORMAL', 4);
            define('ACCOUNT_TYPE_WEBAPP_NORMAL', 5);
            define('ACCOUNT_TYPE_PHONEAPP_NORMAL', 6);
            define('ACCOUNT_TYPE_APP_AUTH', 7);
            define('ACCOUNT_TYPE_WXAPP_WORK', 8);
            define('ACCOUNT_TYPE_XZAPP_NORMAL', 9);
            define('ACCOUNT_TYPE_XZAPP_AUTH', 10);
            define('ACCOUNT_TYPE_ALIAPP_NORMAL', 11);
            define('ACCOUNT_TYPE_SIGN', 'account');
            define('WXAPP_TYPE_SIGN', 'wxapp');
            define('WEBAPP_TYPE_SIGN', 'webapp');
            define('PHONEAPP_TYPE_SIGN', 'phoneapp');
            define('WELCOMESYSTEM_TYPE_SIGN', 'welcome');
            define('XZAPP_TYPE_SIGN', 'xzapp');
            define('ALIAPP_TYPE_SIGN', 'aliapp');

            define('ACCOUNT_OAUTH_LOGIN', 3);
            define('ACCOUNT_NORMAL_LOGIN', 1);

            define('WEIXIN_ROOT', 'https://mp.weixin.qq.com');

            define('ACCOUNT_OPERATE_ONLINE', 1);
            define('ACCOUNT_OPERATE_MANAGER', 2);
            define('ACCOUNT_OPERATE_CLERK', 3);

            define('ACCOUNT_MANAGE_NAME_CLERK', 'clerk');
            define('ACCOUNT_MANAGE_TYPE_OPERATOR', 1);
            define('ACCOUNT_MANAGE_NAME_OPERATOR', 'operator');
            define('ACCOUNT_MANAGE_TYPE_MANAGER', 2);
            define('ACCOUNT_MANAGE_NAME_MANAGER', 'manager');
            define('ACCOUNT_MANAGE_TYPE_OWNER', 3);
            define('ACCOUNT_MANAGE_NAME_OWNER', 'owner');
            define('ACCOUNT_MANAGE_NAME_FOUNDER', 'founder');
            define('ACCOUNT_MANAGE_GROUP_FOUNDER', 1);
            define('ACCOUNT_MANAGE_TYPE_VICE_FOUNDER', 4);
            define('ACCOUNT_MANAGE_NAME_VICE_FOUNDER', 'vice_founder');
            define('ACCOUNT_MANAGE_GROUP_VICE_FOUNDER', 2);
            define('ACCOUNT_MANAGE_GROUP_GENERAL', 0);
            define('ACCOUNT_MANAGE_NAME_UNBIND_USER', 'unbind_user');
            define('ACCOUNT_NO_OWNER_UID', 0);
            define('ACCOUNT_MANAGE_NAME_EXPIRED', 'expired');

            define('SYSTEM_COUPON', 1);
            define('WECHAT_COUPON', 2);
            define('COUPON_TYPE_DISCOUNT', '1');
            define('COUPON_TYPE_CASH', '2');
            define('COUPON_TYPE_GROUPON', '3');
            define('COUPON_TYPE_GIFT', '4');
            define('COUPON_TYPE_GENERAL', '5');
            define('COUPON_TYPE_MEMBER', '6');
            define('COUPON_TYPE_SCENIC', '7');
            define('COUPON_TYPE_MOVIE', '8');
            define('COUPON_TYPE_BOARDINGPASS', '9');
            define('COUPON_TYPE_MEETING', '10');
            define('COUPON_TYPE_BUS', '11');
            define('ATTACH_FTP', 1);
            define('ATTACH_OSS', 2);
            define('ATTACH_QINIU', 3);
            define('ATTACH_COS', 4);
            define('ATTACH_TYPE_IMAGE', 1);
            define('ATTACH_TYPE_VOICE', 2);
            define('ATTACH_TYPE_VEDIO', 3);
            define('ATTACH_TYPE_NEWS', 4);

            define('ATTACHMENT_IMAGE', 'image');

            define('ATTACH_SAVE_TYPE_FIXED', 1);
            define('ATTACH_SAVE_TYPE_TEMP', 2);

            define('STATUS_OFF', 0);
            define('STATUS_ON', 1);
            define('STATUS_SUCCESS', 0);
            define('CACHE_EXPIRE_SHORT', 60);
            define('CACHE_EXPIRE_MIDDLE', 300);
            define('CACHE_EXPIRE_LONG', 3600);
            define('CACHE_KEY_LENGTH', 100);
            define('MODULE_SUPPORT_WXAPP', 2);
            define('MODULE_NONSUPPORT_WXAPP', 1);
            define('MODULE_SUPPORT_ACCOUNT', 2);
            define('MODULE_NONSUPPORT_ACCOUNT', 1);
            define('MODULE_NOSUPPORT_WEBAPP', 1);
            define('MODULE_SUPPORT_WEBAPP', 2);
            define('MODULE_NOSUPPORT_PHONEAPP', 1);
            define('MODULE_SUPPORT_PHONEAPP', 2);
            define('MODULE_SUPPORT_SYSTEMWELCOME', 2);
            define('MODULE_NONSUPPORT_SYSTEMWELCOME', 1);
            define('MODULE_NOSUPPORT_ANDROID', 1);
            define('MODULE_SUPPORT_ANDROID', 2);
            define('MODULE_NOSUPPORT_IOS', 1);
            define('MODULE_SUPPORT_IOS', 2);
            define('MODULE_SUPPORT_XZAPP', 2);
            define('MODULE_NOSUPPORT_XZAPP', 1);
            define('MODULE_SUPPORT_ALIAPP', 2);
            define('MODULE_NOSUPPORT_ALIAPP', 1);

            define('MODULE_SUPPORT_WXAPP_NAME', 'wxapp_support');
            define('MODULE_SUPPORT_ACCOUNT_NAME', 'account_support');
            define('MODULE_SUPPORT_WEBAPP_NAME', 'webapp_support');
            define('MODULE_SUPPORT_PHONEAPP_NAME', 'phoneapp_support');
            define('MODULE_SUPPORT_SYSTEMWELCOME_NAME', 'welcome_support');
            define('MODULE_SUPPORT_XZAPP_NAME', 'xzapp_support');
            define('MODULE_SUPPORT_ALIAPP_NAME', 'aliapp_support');

            define('MODULE_LOCAL_INSTALL', '1');
            define('MODULE_LOCAL_UNINSTALL', '2');
            define('MODULE_CLOUD_INSTALL', '3');
            define('MODULE_CLOUD_UNINSTALL', '4');
            define('MODULE_RECYCLE_INSTALL_DISABLED', '1');
            define('MODULE_RECYCLE_UNINSTALL_IGNORE', '2');

            define('PERMISSION_ACCOUNT', 'system');
            define('PERMISSION_WXAPP', 'wxapp');
            define('PERMISSION_SYSTEM', 'site');

            define('PAYMENT_WECHAT_TYPE_NORMAL', 1);
            define('PAYMENT_WECHAT_TYPE_BORROW', 2);
            define('PAYMENT_WECHAT_TYPE_SERVICE', 3);
            define('PAYMENT_WECHAT_TYPE_CLOSE', 4);

            define('FANS_CHATS_FROM_SYSTEM', 1);

            define('WXAPP_STATISTICS_DAILYVISITTREND', 2);
            define('WXAPP_DIY', 1);
            define('WXAPP_TEMPLATE', 2);
            define('WXAPP_MODULE', 3);
            define('WXAPP_CREATE_MODULE', 1);
            define('WXAPP_CREATE_MUTI_MODULE', 2);
            define('WXAPP_CREATE_DEFAULT', 0);

            define('MATERIAL_LOCAL', 'local');
            define('MATERIAL_WEXIN', 'perm');
            define('MENU_CURRENTSELF', 1);
            define('MENU_HISTORY', 2);
            define('MENU_CONDITIONAL', 3);

            define('USER_STATUS_CHECK', 1);
            define('USER_STATUS_NORMAL', 2);
            define('USER_STATUS_BAN', 3);

            define('USER_TYPE_COMMON', 1);
            define('USER_TYPE_CLERK', 3);

            define('PERSONAL_BASE_TYPE', 1);
            define('PERSONAL_AUTH_TYPE', 2);
            define('PERSONAL_LIST_TYPE', 3);

            define('STORE_TYPE_MODULE', 1);
            define('STORE_TYPE_ACCOUNT', 2);
            define('STORE_TYPE_WXAPP', 3);
            define('STORE_TYPE_WXAPP_MODULE', 4);
            define('STORE_TYPE_PACKAGE', 5);
            define('STORE_TYPE_API', 6);
            define('STORE_TYPE_ACCOUNT_RENEW', 7);
            define('STORE_TYPE_WXAPP_RENEW', 8);
            define('STORE_TYPE_USER_PACKAGE', 9);
            define('STORE_ORDER_PLACE', 1);
            define('STORE_ORDER_DELETE', 2);
            define('STORE_ORDER_FINISH', 3);
            define('STORE_ORDER_DEACTIVATE', 4);
            define('STORE_GOODS_STATUS_OFFlINE', 0);
            define('STORE_GOODS_STATUS_ONLINE', 1);
            define('STORE_GOODS_STATUS_DELETE', 2);

            define('ARTICLE_PCATE', 0);
            define('ARTICLE_CCATE', 0);

            define('USER_REGISTER_TYPE_QQ', 1);
            define('USER_REGISTER_TYPE_WECHAT', 2);
            define('USER_REGISTER_TYPE_MOBILE', 3);

            define('MESSAGE_ORDER_TYPE', 1);
            define('MESSAGE_ORDER_PAY_TYPE', 9);
            define('MESSAGE_ACCOUNT_EXPIRE_TYPE', 2);
            define('MESSAGE_WECHAT_EXPIRE_TYPE', 5);
            define('MESSAGE_WEBAPP_EXPIRE_TYPE', 6);
            define('MESSAGE_WORKORDER_TYPE', 3);
            define('MESSAGE_REGISTER_TYPE', 4);
            define('MESSAGE_USER_EXPIRE_TYPE', 7);
            define('MESSAGE_WXAPP_MODULE_UPGRADE', 8);
            define('MESSAGE_SYSTEM_UPGRADE', 10);
            define('MESSAGE_OFFICIAL_DYNAMICS', 11);

            define('MESSAGE_ENABLE', 1);
            define('MESSAGE_DISABLE', 2);

            define('MESSAGE_NOREAD', 1);
            define('MESSAGE_READ', 2);

            define('FILE_NO_UNIACID', -1);

            define('OAUTH_TYPE_BASE', 1);
            define('OAUTH_TYPE_USERINFO', 2);

            define('ARTICLE_COMMENT_DEFAULT', 0);
            define('ARTICLE_NOCOMMENT', 1);
            define('ARTICLE_COMMENT', 2);
            define('ARTICLE_COMMENT_NOREAD', 1);
            define('ARTICLE_COMMENT_READ', 2);

            define('COMMENT_STATUS_OFF', 0);
            define('COMMENT_STATUS_ON', 1);

            define('WELCOME_DISPLAY_TYPE', 9);
            define('LASTVISIT_DISPLAY_TYPE', 1);
            define('ACCOUNT_DISPLAY_TYPE', 2);
            define('WXAPP_DISPLAY_TYPE', 3);
            define('WEBAPP_DISPLAY_TYPE', 4);
            define('PHONEAPP_DISPLAY_TYPE', 5);
            define('PLATFORM_DISPLAY_TYPE', 6);
            define('MODULE_DISPLAY_TYPE', 7);
        }

        public function init()
        {
            global $_W, $_GPC;
            $_W = $_GPC = [];

            load()->func('global');
            define('CLIENT_IP', getip());

            load()->classs('sqlparser');
            load()->func('pdo');
            load()->func('communication'); // TODO 替换

            $_W['timestamp'] = TIMESTAMP;
            $_W['charset'] = Yii::$app->charset;
            $_W['clientip'] = CLIENT_IP;
            $_W['ishttps'] = Yii::$app->request->isSecureConnection;
            $_W['isajax'] = Yii::$app->request->isAjax;
            $_W['ispost'] = $_SERVER['REQUEST_METHOD'] === 'POST';
            $_W['sitescheme'] = $_W['ishttps'] ? 'https://' : 'http://';
            $_W['script_name'] = htmlspecialchars(scriptname());
            $_W['siteroot'] = home_url() . '/'; // TODO 切换Yii方式
            $_W['siteurl'] = home_url(add_query_arg([]));

            load()->library('agent');
            $type = \Agent::deviceType();
            if ($type == \Agent::DEVICE_MOBILE) {
                $_W['os'] = 'mobile';
            } elseif ($type == \Agent::DEVICE_DESKTOP) {
                $_W['os'] = 'windows';
            } else {
                $_W['os'] = 'unknown';
            }

            $type = \Agent::browserType();
            if (\Agent::isMicroMessage() == \Agent::MICRO_MESSAGE_YES) {
                $_W['container'] = 'wechat';
            } elseif ($type == \Agent::BROWSER_TYPE_ANDROID) {
                $_W['container'] = 'android';
            } elseif ($type == \Agent::BROWSER_TYPE_IPAD) {
                $_W['container'] = 'ipad';
            } elseif ($type == \Agent::BROWSER_TYPE_IPHONE) {
                $_W['container'] = 'iphone';
            } elseif ($type == \Agent::BROWSER_TYPE_IPOD) {
                $_W['container'] = 'ipod';
            } else {
                $_W['container'] = 'unknown';
            }

            // TODO 目前全部接受COOKIE, 需过滤cookiepre?
            $_GPC = array_merge($_GPC, $_GET, $_POST, $_COOKIE);
            if (!$_W['isajax']) {
                $input = file_get_contents("php://input");
                if (!empty($input)) {
                    $__input = @json_decode($input, true);
                    if (!empty($__input)) {
                        $_GPC['__input'] = $__input;
                        $_W['isajax'] = true;
                    }
                }
            }
        }
    }
}