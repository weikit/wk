<?php // TODO 待优化

function cache_read($key)
{
    $cachedata = wp_cache_get($key, 'plugins');

    return $cachedata === false ? '' : $cachedata;
}

function cache_search($prefix)
{
    unsupported();
}

function cache_write($key, $data, $expire = 0)
{
    if (empty($key) || ! isset($data)) {
        return false;
    }

    return wp_cache_set($key, $data, 'plugins', $expire);
}

function cache_delete($key)
{
    $cache_relation_keys = cache_relation_keys($key);
    if (is_error($cache_relation_keys)) {
        return $cache_relation_keys;
    }
    if (is_array($cache_relation_keys) && ! empty($cache_relation_keys)) {
        foreach ($cache_relation_keys as $key) {
            $cache_info = cache_load($key);
            if ( ! empty($cache_info)) {
                wp_cache_delete($key, 'plugins');
            }
        }

        return true;
    }
}

function cache_clean($prefix = '')
{
    if (empty($prefix)) {
        wp_cache_flush();
    } else {
        $cache_relation_keys = cache_relation_keys($prefix);
        if (is_error($cache_relation_keys)) {
            return $cache_relation_keys;
        }

        if (is_array($cache_relation_keys) && ! empty($cache_relation_keys)) {
            foreach ($cache_relation_keys as $key) { // TODO 待完善
                $cache_info = cache_load($key);
                if ( ! empty($cache_info)) {
                    preg_match_all('/\:([a-zA-Z0-9\-\_]+)/', $key, $matches);
                    $sql            = "DELETE FROM " . tablename('core_cache') . ' WHERE `key` LIKE :key';
                    $params         = [];
                    $params[':key'] = "wk:{$matches[1][0]}%";
                    $result         = pdo_query($sql, $params);
                    if ( ! $result) {
                        return error(-1, '缓存 ' . $key . '删除失败!');
                    }
                }
            }
        }
    }

    return true;
}

function cache_type()
{
    unsupported();
}

function cache_load($key, $unserialize = false)
{
    if (is_error($key)) {
        trigger_error($key['message'], E_USER_WARNING);

        return false;
    }

    static $cache;
    if ( ! empty($cache[$key])) {
        return $cache[$key];
    }

    $data = $cache[$key] = cache_read($key);

    global $_W;
    if ($key == 'setting') { // TODO remove
        $_W['setting'] = $data;

        return $_W['setting'];
    } elseif ($key == 'modules') {
        $_W['modules'] = $data;

        return $_W['modules'];
    } elseif ($key == 'module_receive_enable' && empty($data)) {
        cache_build_module_subscribe_type();

        return cache_read($key);
    } else {
        return $unserialize ? iunserializer($data) : $data;
    }
}

function &cache_global($key)
{

}

function cache_system_key($cache_key)
{
    $cache_key_all = cache_key_all();

    $params = [];
    $args   = func_get_args();
    if ( ! is_array($args[1])) {
        $cache_key = $cache_key_all['caches'][$cache_key]['key'];
        preg_match_all('/\%([a-zA-Z\_\-0-9]+)/', $cache_key, $matches);
        for ($i = 0; $i < func_num_args() - 1; $i++) {
            $cache_key = str_replace($matches[0][$i], $args[$i + 1], $cache_key);
        }

        return 'wk:' . $cache_key;
    } else {
        $params = $args[1];
    }

    if (empty($params)) {
        $res = preg_match_all('/([a-zA-Z\_\-0-9]+):/', $cache_key, $matches);
        if ($res) {
            $key = count($matches[1]) > 0 ? $matches[1][0] : $matches[1];
        } else {
            $key = $cache_key;
        }
        if (empty($cache_key_all['caches'][$key])) {
            return error(1, '缓存' . $key . ' 不存在!');
        } else {
            $cache_info_key = $cache_key_all['caches'][$key]['key'];
            preg_match_all('/\%([a-zA-Z\_\-0-9]+)/', $cache_info_key, $key_params);
            preg_match_all('/\:([a-zA-Z\_\-0-9]+)/', $cache_key, $val_params);

            if (count($key_params[1]) != count($val_params[1])) {
                foreach ($key_params[1] as $key => $val) {
                    if (in_array($val, array_keys($cache_key_all['common_params']))) {
                        $cache_info_key = str_replace('%' . $val, $cache_key_all['common_params'][$val],
                            $cache_info_key);
                        unset($key_params[1][$key]);
                    }
                }

                if (count($key_params[1]) == count($val_params[1])) {
                    $arr = array_combine($key_params[1], $val_params[1]);
                    foreach ($arr as $key => $val) {
                        if (preg_match('/\%' . $key . '/', $cache_info_key)) {
                            $cache_info_key = str_replace('%' . $key, $val, $cache_info_key);
                        }
                    }
                }

                if (strexists($cache_info_key, '%')) {
                    return error(1, '缺少缓存参数或参数不正确!');
                } else {
                    return 'wk:' . $cache_info_key;
                }
            } else {
                return 'wk:' . $cache_key;
            }
        }
    }

    $cache_info          = $cache_key_all['caches'][$cache_key];
    $cache_common_params = $cache_key_all['common_params'];

    if (empty($cache_info)) {
        return error(2, '缓存 ' . $cache_key . ' 不存在!');
    } else {
        $cache_key = $cache_info['key'];
    }

    foreach ($cache_common_params as $param_name => $param_val) {
        preg_match_all('/\%([a-zA-Z\_\-0-9]+)/', $cache_key, $matches);
        if (in_array($param_name, $matches[1]) && ! in_array($param_name, array_keys($params))) {
            $params[$param_name] = $cache_common_params[$param_name];
        }
    }

    if (is_array($params) && ! empty($params)) {
        foreach ($params as $key => $param) {
            $cache_key = str_replace('%' . $key, $param, $cache_key);
        }

        if (strexists($cache_key, '%')) {
            return error(1, '缺少缓存参数或参数不正确!');
        }
    }

    $cache_key = 'wk:' . $cache_key;
    if (strlen($cache_key) > CACHE_KEY_LENGTH) {
        trigger_error('Cache name is over the maximum length');
    }

    return $cache_key;
}

function cache_relation_keys($key)
{
    if ( ! is_string($key)) {
        return $key;
    }

    if ( ! strexists($key, 'wk:')) {
        return [$key];
    }

    $cache_param_values = explode(':', $key);
    $cache_name         = $cache_param_values[1];
    unset($cache_param_values[0]);
    unset($cache_param_values[1]);

    if (empty($cache_param_values)) {
        preg_match_all('/\:([a-zA-Z\_\-0-9]+)/', $key, $matches);
        $cache_name = $matches[1][0];
    }

    $cache_key_all       = cache_key_all();
    $cache_relations     = $cache_key_all['groups'];
    $cache_common_params = $cache_key_all['common_params'];

    $cache_info = $cache_key_all['caches'][$cache_name];

    if (empty($cache_info)) {
        return error(2, '缓存' . $key . '不存在.');
    }

    if ( ! empty($cache_info['group'])) {
        if (empty($cache_relations[$cache_info['group']])) {
            return error(1, '关联关系未定义');
        }
        $relation_keys = $cache_relations[$cache_info['group']]['relations'];
        $cache_keys    = [];

        foreach ($relation_keys as $key => $val) {
            if ($val == $cache_name) {
                $relation_cache_key = $cache_key_all['caches'][$val]['key'];
            } else {
                $relation_cache_key = $cache_key_all['caches'][$cache_name]['key'];
            }

            foreach ($cache_common_params as $param_name => $param_val) {
                preg_match_all('/\%([a-zA-Z\_\-0-9]+)/', $relation_cache_key, $matches);
                if (in_array($param_name, $matches[1])) {
                    $cache_key_params[$param_name] = $cache_common_params[$param_name];
                }
                $cache_key_params = array_combine($matches[1], $cache_param_values);
            }

            $cache_key = cache_system_key($val, $cache_key_params);
            if ( ! is_error($cache_key)) {
                $cache_keys[] = $cache_key;
            } else {
                return error(1, $cache_key['message']);
            }
        }
    } else {
        $cache_keys[] = $key;
    }

    return $cache_keys;
}

function cache_key_all()
{
    global $_W;
    static $caches;
    if ($caches === null) {
        $caches = [
            'common_params' => [
                'uniacid' => $_W['uniacid'] ?? 0,
                'uid'     => $_W['uid'] ?? 0,
            ],

            'caches' => [
                'module_info' => [
                    'key'   => 'module_info:%module_name',
                    'group' => 'module',
                ],

                'module_setting' => [
                    'key'   => 'module_setting:%module_name:%uniacid',
                    'group' => 'module',
                ],

                'last_account' => [
                    'key'   => 'last_account:%switch',
                    'group' => '',
                ],

                'last_account_type' => [
                    'key'   => 'last_account_type',
                    'group' => '',
                ],

                'user_modules' => [
                    'key'   => 'user_modules:%uid',
                    'group' => '',
                ],

                'user_accounts' => [
                    'key'   => 'user_accounts:%type:%uid',
                    'group' => '',
                ],

                'unimodules' => [
                    'key'   => 'unimodules:%uniacid:%enabled',
                    'group' => '',
                ],

                'unimodules_binding' => [
                    'key'   => 'unimodules_binding:%uniacid',
                    'group' => '',
                ],

                'uni_groups' => [
                    'key'   => 'uni_groups',
                    'group' => '',
                ],

                'permission' => [
                    'key'   => 'permission:%uniacid:%uid',
                    'group' => '',
                ],

                'memberinfo' => [
                    'key'   => 'memberinfo:%uid',
                    'group' => '',
                ],

                'statistics' => [
                    'key'   => 'statistics:%uniacid',
                    'group' => '',
                ],

                'uniacid_visit' => [
                    'key'   => 'uniacid_visit:%uniacid:%today',
                    'group' => '',
                ],

                'material_reply' => [
                    'key'   => 'material_reply:%attach_id',
                    'group' => '',
                ],

                'keyword' => [
                    'key'   => 'keyword:%content:%uniacid',
                    'group' => '',
                ],

                'back_days' => [
                    'key'   => 'back_days',
                    'group' => '',
                ],

                'wxapp_version' => [
                    'key'   => 'wxapp_version:%version_id',
                    'group' => '',
                ],

                'site_store_buy' => [
                    'key'   => 'site_store_buy:%type:%uniacid',
                    'group' => '',
                ],

                'proxy_wechatpay_account' => [
                    'key'   => 'proxy_wechatpay_account',
                    'group' => '',
                ],

                'recycle_module' => [
                    'key'   => 'recycle_module',
                    'group' => '',
                ],

                'sync_fans_pindex' => [
                    'key'   => 'sync_fans_pindex:%uniacid',
                    'group' => '',
                ],

                'uniaccount' => [
                    'key'   => "uniaccount:%uniacid",
                    'group' => 'uniaccount',
                ],

                'unisetting' => [
                    'key'   => "unisetting:%uniacid",
                    'group' => 'uniaccount',
                ],

                'defaultgroupid' => [
                    'key'   => 'defaultgroupid:%uniacid',
                    'group' => 'uniaccount',
                ],

                'uniaccount_type' => [
                    'key'   => "uniaccount_type:%account_type",
                    'group' => '',
                ],


                'accesstoken' => [
                    'key'   => 'accesstoken:%acid',
                    'group' => 'accesstoken',
                ],

                'jsticket' => [
                    'key'   => 'jsticket:%acid',
                    'group' => 'accesstoken',
                ],

                'cardticket' => [
                    'key'   => 'cardticket:%acid',
                    'group' => 'accesstoken',
                ],


                'accesstoken_key' => [
                    'key'   => 'accesstoken_key:%key',
                    'group' => '',
                ],

                'account_auth_refreshtoken' => [
                    'key'   => 'account_auth_refreshtoken:%acid',
                    'group' => '',
                ],

                'unicount' => [
                    'key'   => 'unicount:%uniacid',
                    'group' => '',
                ],

                'checkupgrade' => [
                    'key'   => 'checkupgrade',
                    'group' => '',
                ],

                'cloud_transtoken' => [
                    'key'   => 'cloud_transtoken',
                    'group' => '',
                ],

                'upgrade' => [
                    'key'   => 'upgrade',
                    'group' => '',
                ],

                'account_ticket' => [
                    'key'   => 'account_ticket',
                    'group' => '',
                ],

                'oauthaccesstoken' => [
                    'key'   => 'oauthaccesstoken:%acid',
                    'group' => '',
                ],

                'account_component_assesstoken' => [
                    'key'   => 'account_component_assesstoken',
                    'group' => '',
                ],

                'cloud_ad_uniaccount' => [
                    'key'   => 'cloud_ad_uniaccount:%uniacid',
                    'group' => '',
                ],

                'cloud_ad_uniaccount_list' => [
                    'key'   => 'cloud_ad_uniaccount_list',
                    'group' => '',
                ],

                'cloud_flow_master' => [
                    'key'   => 'cloud_flow_master',
                    'group' => '',
                ],

                'cloud_ad_tags' => [
                    'key'   => 'cloud_ad_tags',
                    'group' => '',
                ],

                'cloud_ad_type_list' => [
                    'key'   => 'cloud_ad_type_list',
                    'group' => '',
                ],

                'cloud_ad_app_list' => [
                    'key'   => 'cloud_ad_app_list:%uniacid',
                    'group' => '',
                ],

                'cloud_ad_app_support_list' => [
                    'key'   => 'cloud_ad_app_support_list',
                    'group' => '',
                ],

                'cloud_ad_site_finance' => [
                    'key'   => 'cloud_ad_site_finance',
                    'group' => '',
                ],

                'couponsync' => [
                    'key'   => 'couponsync:%uniacid',
                    'group' => '',
                ],

                'storesync' => [
                    'key'   => 'storesync:%uniacid',
                    'group' => '',
                ],

                'cloud_auth_transfer' => [
                    'key'   => 'cloud_auth_transfer',
                    'group' => '',
                ],

                'modulesetting' => [
                    'key'   => 'modulesetting:%module:%acid',
                    'group' => '',
                ],

                'scan_config' => [
                    'key'   => 'scan_config',
                    'group' => 'scan_file',
                ],

                'scan_file' => [
                    'key'   => 'scan_file',
                    'group' => 'scan_file',
                ],

                'scan_badfile' => [
                    'key'   => 'scan_badfile',
                    'group' => 'scan_file',
                ],

                'bomtree' => [
                    'key'   => 'bomtree',
                    'group' => '',
                ],

                'setting' => [
                    'key'   => 'setting',
                    'group' => '',
                ],

                'stat_todaylock' => [
                    'key'   => 'stat_todaylock:%uniacid',
                    'group' => '',
                ],

                'account_preauthcode' => [
                    'key'   => 'account_preauthcode',
                    'group' => '',
                ],

                'account_auth_accesstoken' => [
                    'key'   => 'account_auth_accesstoken:%key',
                    'group' => '',
                ],

                'usersfields' => [
                    'key'   => 'usersfields',
                    'group' => '',
                ],

                'userbasefields' => [
                    'key'   => 'userbasefields',
                    'group' => '',
                ],

                'system_frame' => [
                    'key'   => 'system_frame:%uid',
                    'group' => '',
                ],

                'module_receive_enable' => [
                    'key'   => 'module_receive_enable',
                    'group' => '',
                ],
            ],
            'groups' => [
                'uniaccount' => [
                    'relations' => ['uniaccount', 'unisetting', 'defaultgroupid'],
                ],

                'accesstoken' => [
                    'relations' => ['accesstoken', 'jsticket', 'cardticket'],
                ],

                'scan_file' => [
                    'relations' => ['scan_file', 'scan_config', 'scan_badfile'],
                ],

                'module' => [
                    'relations' => ['module_info', 'module_setting'],
                ],
            ],
        ];
    }

    return $caches;
}
