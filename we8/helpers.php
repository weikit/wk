<?php

defined('ABSPATH') || exit;

class Loader
{
    /**
     * @var array
     */
    protected $cache = [];
    /**
     * @var array
     */
    protected $singletons = [];
    /**
     * @var array
     */
    protected $libraries = [
        'agent'     => 'agent/agent.class',
        'captcha'   => 'captcha/captcha.class',
        'pdo'       => 'pdo/PDO.class',
        'qrcode'    => 'qrcode/phpqrcode',
        'ftp'       => 'ftp/ftp',
        'pinyin'    => 'pinyin/pinyin',
        'pkcs7'     => 'pkcs7/pkcs7Encoder',
        'json'      => 'json/JSON',
        'phpmailer' => 'phpmailer/PHPMailerAutoload',
        'oss'       => 'alioss/autoload',
        'qiniu'     => 'qiniu/autoload',
        'cos'       => 'cosv4.2/include',
        'cosv3'     => 'cos/include',
        'sentry'    => 'sentry/Raven/Autoloader',
    ];
    /**
     * @var array
     */
    protected $types = [
        'func'    => '/framework/function/%s.func.php',
        'model'   => '/framework/model/%s.mod.php',
        'classs'  => '/framework/class/%s.class.php',
        'library' => '/framework/library/%s.php',
        'table'   => '/framework/table/%s.table.php',
        'web'     => '/web/common/%s.func.php',
        'app'     => '/app/common/%s.func.php',
    ];

    /**
     * @param string $type
     * @param array $params
     *
     * @return bool
     */
    public function __call($type, $params)
    {
        $name = $cacheKey = array_shift($params);
        if (isset($this->cache[$type][$cacheKey]) || empty($this->libraries[$type])) {
            return true;
        } elseif ($type === 'library' && ! empty($this->libraries[$name])) {
            $name = $this->libraries[$name];
        }

        $file = sprintf($this->types[$type], $name);
        if ( ! file_exists(WE8_PATH . $file)) {
            throw new RuntimeException("Invalid file '{$file}' of type ${$type}");
        }

        include WE8_PATH . $file;
        $this->cache[$type][$cacheKey] = true;

        return $this->cache[$type][$cacheKey] = true;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function singleton($name)
    {
        if ( ! isset($this->singletons[$name])) {
            $this->singletons[$name] = $this->object($name);
        }

        return $this->singletons[$name];
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function object($name)
    {
        $this->classs(strtolower($name));

        return class_exists($name) ? new $name() : false;
    }
}

/**
 * 加载辅助器
 *
 * @return Loader
 */
function load()
{
    static $loader;

    if ($loader === null) {
        $loader = Yii::createObject(Loader::class);
    }

    return $loader;
}

/**
 * 严格比对版本号
 *
 * @param string|int $a
 * @param string|int $b
 *
 * @return mixed
 */
function ver_compare($a, $b)
{
    $a = str_replace('.', '', $a);
    $b = str_replace('.', '', $b);

    $len = max([strlen($a), strlen($b)]);


    $a = (int)str_pad($a, $len, '0', STR_PAD_RIGHT);
    $b = (int)str_pad($b, $len, '0', STR_PAD_RIGHT);

    return version_compare($a, $b);
}

/**
 * 转义字符串(数组递归转义)
 *
 * @param array|string $value
 *
 * @return array|string
 */
function istripslashes($value)
{
    return is_array($value) ? array_map('istripslashes', $value) : stripslashes($value);
}

/**
 * 转换特殊html字符(数组递归转换)
 *
 * @param array|string $value
 *
 * @return array|string
 */
function ihtmlspecialchars($value)
{
    return is_array($value) ?
        array_map('ihtmlspecialchars', $value) :
        str_replace('&amp;', '&', htmlspecialchars($value, ENT_QUOTES));
}

// TODO
function isetcookie($key, $value, $expire = 0, $httponly = false)
{
    global $_W;
    $expire = $expire != 0 ? (TIMESTAMP + $expire) : 0;
    $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;

    return setcookie($_W['config']['cookie']['pre'] . $key, $value, $expire, $_W['config']['cookie']['path'],
        $_W['config']['cookie']['domain'], $secure, $httponly);
}

/**
 * 获取真实IP地址
 *
 * @return string
 */
function getip()
{
    static $ip;
    if ($ip === null) {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s',
                $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $_ip) {
                if ( ! preg_match('#^(10|172\.16|192\.168)\.#', $_ip)) {
                    $ip = $_ip;
                    break;
                }
            }
        }
        if ( ! preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip)) {
            $ip = '127.0.0.1';
        }
    }

    return $ip;
}

// TODO
function token($specialadd = '')
{
    global $_W;
    if ( ! defined('IN_MOBILE')) {
        return substr(md5($_W['config']['setting']['authkey'] . $specialadd), 8, 8);
    } else {
        if ( ! empty($_SESSION['token'])) {
            $count = count($_SESSION['token']) - 5;
            asort($_SESSION['token']);
            foreach ($_SESSION['token'] as $k => $v) {
                if (TIMESTAMP - $v > 300 || $count > 0) {
                    unset($_SESSION['token'][$k]);
                    $count--;
                }
            }
        }
        $key = substr(random(20), 0, 4);
        $_SESSION['token'][$key] = TIMESTAMP;

        return $key;
    }
}

/**
 * 生成随机字符串或随机随机数
 *
 * @param int $length
 * @param bool $numeric
 *
 * @return string
 * @throws \yii\base\Exception
 */
function random($length, $numeric = false)
{
    if ($numeric === false) {
        return Yii::$app->security->generateRandomString($length);
    } else {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }
}

// TODO
function checksubmit($var = 'submit', $allowget = false)
{
    global $_W, $_GPC;
    if (empty($_GPC[$var])) {
        return false;
    }
    if (defined('IN_SYS')) {
        if ($allowget || (($_W['ispost'] && ! empty($_W['token']) && $_W['token'] == $_GPC['token']) && (empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i",
                        "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1",
                        $_SERVER['HTTP_HOST'])))) {
            return true;
        }
    } else {
        if (empty($_W['isajax']) && empty($_SESSION['token'][$_GPC['token']])) {
            exit("<script type=\"text/javascript\">history.go(-1);</script>");
        } else {
            unset($_SESSION['token'][$_GPC['token']]);
        }

        return true;
    }

    return false;
}

// TODO
function checkcaptcha($code)
{
    global $_W, $_GPC;
    session_start();
    $codehash = md5(strtolower($code) . $_W['config']['setting']['authkey']);
    if ( ! empty($_GPC['__code']) && $codehash == $_SESSION['__code']) {
        $return = true;
    } else {
        $return = false;
    }
    $_SESSION['__code'] = '';
    isetcookie('__code', '');

    return $return;
}

/**
 * 获取带前缀的数据库名
 *
 * @param $table
 *
 * @return string
 */
function tablename($table)
{
    return '`' . Yii::$app->db->tablePrefix . $table . '`';
}

function array_elements($keys, $src, $default = false)
{
    $return = [];
    if ( ! is_array($keys)) {
        $keys = [$keys];
    }
    foreach ($keys as $key) {
        if (isset($src[$key])) {
            $return[$key] = $src[$key];
        } else {
            $return[$key] = $default;
        }
    }

    return $return;
}

/**
 * 对多维数组排序
 *
 * @param array $array
 * @param $key
 * @param string $type
 *
 * @return mixed
 */
function iarray_sort($array, $key, $type = 'asc')
{
    \yii\helpers\ArrayHelper::multisort($array, $key, $type == 'asc' ? SORT_ASC : SORT_DESC);

    return $array;
}

/**
 * 查询数字是否在最小和最大边界值内. 超出则返回边界值
 *
 * @param int $num
 * @param int $min
 * @param int $max
 * @param bool $limit
 *
 * @return bool|int
 */
function range_limit($num, $min, $max, $limit = true)
{
    $num = intval($num);
    $min = intval($min);
    $max = intval($max);
    $limit = $limit === true;
    if ($num < $min) {
        return ! $limit ? false : $min;
    } elseif ($num > $max) {
        return ! $limit ? false : $max;
    } else {
        return ! $limit ? true : $num;
    }
}

/**
 * json转码
 *
 * @param mixed $value
 * @param int $options
 *
 * @return bool|string
 */
function ijson_encode($value, $options = 0)
{
    return empty($value) ? false : addslashes(json_encode($value, $options));
}

/**
 * 序列化数据
 *
 * @param $value
 *
 * @return string
 */
function iserializer($value)
{
    return serialize($value);
}

/**
 * 解码序列化数据(优化中文序列化问题)
 *
 * @param $value
 *
 * @return array|mixed
 */
function iunserializer($value)
{
    if (empty($value)) {
        return [];
    } elseif ( ! is_serialized($value)) {
        return $value;
    }
    if (($result = unserialize($value)) === false) {
        $temp = preg_replace_callback('#s:(\d+):"(.*?)";#s', function ($match) {
            return 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
        }, $value);
        return unserialize($temp);
    } else {
        return $result;
    }
}

/**
 * 判断字符串是base6编码
 *
 * @param string $str
 *
 * @return bool
 */
function is_base64($str)
{
    return is_string($str) && $str == base64_encode(base64_decode($str));
}

// TODO
function wurl($segment, $params = [])
{
    $params[0] = $segment;

    return yii\helpers\Url::to($params);
}

// TODO
if ( ! function_exists('murl')) {

    function murl($segment, $params = [], $noredirect = true, $addhost = false)
    {
        global $_W;
        list($controller, $action, $do) = explode('/', $segment);
        if ( ! empty($addhost)) {
            $url = $_W['siteroot'] . 'app/';
        } else {
            $url = './';
        }
        $str = '';
        if (uni_is_multi_acid()) {
            $str .= "&j={$_W['acid']}";
        }
        if ( ! empty($_W['account']) && $_W['account']['type'] == ACCOUNT_TYPE_WEBAPP_NORMAL) {
            $str .= '&a=webapp';
        }
        if ( ! empty($_W['account']) && $_W['account']['type'] == ACCOUNT_TYPE_PHONEAPP_NORMAL) {
            $str .= '&a=phoneapp';
        }
        $url .= "index.php?i={$_W['uniacid']}{$str}&";
        if ( ! empty($controller)) {
            $url .= "c={$controller}&";
        }
        if ( ! empty($action)) {
            $url .= "a={$action}&";
        }
        if ( ! empty($do)) {
            $url .= "do={$do}&";
        }
        if ( ! empty($params)) {
            $queryString = http_build_query($params, '', '&');
            $url .= $queryString;
            if ($noredirect === false) {
                $url .= '&wxref=mp.weixin.qq.com#wechat_redirect';
            }
        }

        return $url;
    }
}

// TODO
function pagination(
    $total,
    $pageIndex,
    $pageSize = 15,
    $url = '',
    $context = ['before' => 5, 'after' => 4, 'ajaxcallback' => '', 'callbackfuncname' => '']
) {
    global $_W;
    $pdata = [
        'tcount'  => 0,
        'tpage'   => 0,
        'cindex'  => 0,
        'findex'  => 0,
        'pindex'  => 0,
        'nindex'  => 0,
        'lindex'  => 0,
        'options' => '',
    ];
    if (empty($context['before'])) {
        $context['before'] = 5;
    }
    if (empty($context['after'])) {
        $context['after'] = 4;
    }

    if ($context['ajaxcallback']) {
        $context['isajax'] = true;
    }

    if ($context['callbackfuncname']) {
        $callbackfunc = $context['callbackfuncname'];
    }

    $pdata['tcount'] = $total;
    $pdata['tpage'] = (empty($pageSize) || $pageSize < 0) ? 1 : ceil($total / $pageSize);
    if ($pdata['tpage'] <= 1) {
        return '';
    }
    $cindex = $pageIndex;
    $cindex = min($cindex, $pdata['tpage']);
    $cindex = max($cindex, 1);
    $pdata['cindex'] = $cindex;
    $pdata['findex'] = 1;
    $pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
    $pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
    $pdata['lindex'] = $pdata['tpage'];

    if ($context['isajax']) {
        if (empty($url)) {
            $url = $_W['script_name'] . '?' . http_build_query($_GET);
        }
        $pdata['faa'] = 'href="javascript:;" page="' . $pdata['findex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['findex'] . '\', this);"' : '');
        $pdata['paa'] = 'href="javascript:;" page="' . $pdata['pindex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['pindex'] . '\', this);"' : '');
        $pdata['naa'] = 'href="javascript:;" page="' . $pdata['nindex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['nindex'] . '\', this);"' : '');
        $pdata['laa'] = 'href="javascript:;" page="' . $pdata['lindex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['lindex'] . '\', this);"' : '');
    } else {
        if ($url) {
            $pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
            $pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
            $pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
            $pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
        } else {
            $_GET['page'] = $pdata['findex'];
            $pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['pindex'];
            $pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['nindex'];
            $pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['lindex'];
            $pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
        }
    }

    $html = '<div><ul class="pagination pagination-centered">';
    $html .= "<li><a {$pdata['faa']} class=\"pager-nav\">首页</a></li>";
    empty($callbackfunc) && $html .= "<li><a {$pdata['paa']} class=\"pager-nav\">&laquo;上一页</a></li>";

    if ( ! $context['before'] && $context['before'] != 0) {
        $context['before'] = 5;
    }
    if ( ! $context['after'] && $context['after'] != 0) {
        $context['after'] = 4;
    }

    if ($context['after'] != 0 && $context['before'] != 0) {
        $range = [];
        $range['start'] = max(1, $pdata['cindex'] - $context['before']);
        $range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
        if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
            $range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
            $range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
        }
        for ($i = $range['start']; $i <= $range['end']; $i++) {
            if ($context['isajax']) {
                $aa = 'href="javascript:;" page="' . $i . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $i . '\', this);"' : '');
            } else {
                if ($url) {
                    $aa = 'href="?' . str_replace('*', $i, $url) . '"';
                } else {
                    $_GET['page'] = $i;
                    $aa = 'href="?' . http_build_query($_GET) . '"';
                }
            }
            if ( ! empty($context['isajax'])) {
                $html .= ($i == $pdata['cindex'] ? '<li class="active">' : '<li>') . "<a {$aa}>" . $i . '</a></li>';
            } else {
                $html .= ($i == $pdata['cindex'] ? '<li class="active"><a href="javascript:;">' . $i . '</a></li>' : "<li><a {$aa}>" . $i . '</a></li>');
            }
        }
    }

    if ($pdata['cindex'] < $pdata['tpage']) {
        empty($callbackfunc) && $html .= "<li><a {$pdata['naa']} class=\"pager-nav\">下一页&raquo;</a></li>";
        $html .= "<li><a {$pdata['laa']} class=\"pager-nav\">尾页</a></li>";
    }
    $html .= '</ul></div>';

    return $html;
}

// TODO
function tomedia($src, $local_path = false)
{
    global $_W;
    if (empty($src)) {
        return '';
    }
    if (strexists($src, "c=utility&a=wxcode&do=image&attach=")) {
        return $src;
    }
    if (strexists($src, 'addons/')) {
        return $_W['siteroot'] . substr($src, strpos($src, 'addons/'));
    }
    if (strexists($src, $_W['siteroot']) && ! strexists($src, '/addons/')) {
        $urls = parse_url($src);
        $src = $t = substr($urls['path'], strpos($urls['path'], 'images'));
    }
    $t = strtolower($src);
    if (strexists($t, 'https://mmbiz.qlogo.cn') || strexists($t, 'http://mmbiz.qpic.cn')) {
        $url = url('utility/wxcode/image', ['attach' => $src]);

        return $_W['siteroot'] . 'web' . ltrim($url, '.');
    }
    if ((substr($t, 0, 7) == 'http://') || (substr($t, 0, 8) == 'https://') || (substr($t, 0, 2) == '//')) {
        return $src;
    }
    if ($local_path || empty($_W['setting']['remote']['type']) || file_exists(IA_ROOT . '/' . $_W['config']['upload']['attachdir'] . '/' . $src)) {
        $src = $_W['siteroot'] . $_W['config']['upload']['attachdir'] . '/' . $src;
    } else {
        $src = $_W['attachurl_remote'] . $src;
    }

    return $src;
}

/**
 * 错误格式
 *
 * @param $errno
 * @param string $message
 *
 * @return array
 */
function error($errno, $message = '')
{
    return [
        'errno'   => $errno,
        'message' => $message,
    ];
}

/**
 * 是否错误数据体
 *
 * @param $data
 *
 * @return bool
 */
function is_error($data)
{
    if (empty($data) || ! is_array($data) || ! array_key_exists('errno', $data) || (array_key_exists('errno',
                $data) && $data['errno'] == 0)) {
        return false;
    } else {
        return true;
    }
}

// TODO
function detect_sensitive_word($string)
{
    $setting = setting_load('sensitive_words');
    if (empty($setting['sensitive_words'])) {
        return false;
    }
    $sensitive_words = $setting['sensitive_words'];
    $blacklist = "/" . implode("|", $sensitive_words) . "/";
    if (preg_match($blacklist, $string, $matches)) {
        return $matches[0];
    }

    return false;
}

// TODO
function referer($default = '')
{
    global $_GPC, $_W;
    $_W['referer'] = ! empty($_GPC['referer']) ? $_GPC['referer'] : $_SERVER['HTTP_REFERER'];;
    $_W['referer'] = substr($_W['referer'], -1) == '?' ? substr($_W['referer'], 0, -1) : $_W['referer'];

    if (strpos($_W['referer'], 'member.php?act=login')) {
        $_W['referer'] = $default;
    }
    $_W['referer'] = $_W['referer'];
    $_W['referer'] = str_replace('&amp;', '&', $_W['referer']);
    $reurl = parse_url($_W['referer']);

    if ( ! empty($reurl['host']) && ! in_array($reurl['host'],
            [$_SERVER['HTTP_HOST'], 'www.' . $_SERVER['HTTP_HOST']]) && ! in_array($_SERVER['HTTP_HOST'],
            [$reurl['host'], 'www.' . $reurl['host']])) {
        $_W['referer'] = $_W['siteroot'];
    } elseif (empty($reurl['host'])) {
        $_W['referer'] = $_W['siteroot'] . './' . $_W['referer'];
    }

    return strip_tags($_W['referer']);
}

/**
 * 查询关键字是否存在字符串中
 *
 * @param $string
 * @param $find
 *
 * @return bool
 */
function strexists($string, $find)
{
    return strpos($string, $find) !== false;
}

/**
 *
 * TODO
 * @param $string
 * @param $length
 * @param bool $havedot
 * @param string $charset
 *
 * @return mixed|string
 */
function cutstr($string, $length, $havedot = false, $charset = '')
{
    global $_W;
    if (empty($charset)) {
        $charset = $_W['charset'];
    }
    if (strtolower($charset) == 'gbk') {
        $charset = 'gbk';
    } else {
        $charset = 'utf8';
    }
    if (istrlen($string, $charset) <= $length) {
        return $string;
    }
    if (function_exists('mb_strcut')) {
        $string = mb_substr($string, 0, $length, $charset);
    } else {
        $pre = '{%';
        $end = '%}';
        $string = str_replace(['&amp;', '&quot;', '&lt;', '&gt;'],
            [$pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end], $string);

        $strcut = '';
        $strlen = strlen($string);

        if ($charset == 'utf8') {
            $n = $tn = $noc = 0;
            while ($n < $strlen) {
                $t = ord($string[$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1;
                    $n++;
                    $noc++;
                } elseif (194 <= $t && $t <= 223) {
                    $tn = 2;
                    $n += 2;
                    $noc++;
                } elseif (224 <= $t && $t <= 239) {
                    $tn = 3;
                    $n += 3;
                    $noc++;
                } elseif (240 <= $t && $t <= 247) {
                    $tn = 4;
                    $n += 4;
                    $noc++;
                } elseif (248 <= $t && $t <= 251) {
                    $tn = 5;
                    $n += 5;
                    $noc++;
                } elseif ($t == 252 || $t == 253) {
                    $tn = 6;
                    $n += 6;
                    $noc++;
                } else {
                    $n++;
                }
                if ($noc >= $length) {
                    break;
                }
            }
            if ($noc > $length) {
                $n -= $tn;
            }
            $strcut = substr($string, 0, $n);
        } else {
            while ($n < $strlen) {
                $t = ord($string[$n]);
                if ($t > 127) {
                    $tn = 2;
                    $n += 2;
                    $noc++;
                } else {
                    $tn = 1;
                    $n++;
                    $noc++;
                }
                if ($noc >= $length) {
                    break;
                }
            }
            if ($noc > $length) {
                $n -= $tn;
            }
            $strcut = substr($string, 0, $n);
        }
        $string = str_replace([$pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end],
            ['&amp;', '&quot;', '&lt;', '&gt;'], $strcut);
    }

    if ($havedot) {
        $string = $string . "...";
    }

    return $string;
}

/**
 * 获取字符串长度
 *
 * @param $str
 * @param null $encoding
 *
 * @return int
 */
function istrlen($str, $encoding = null)
{
    return mb_strlen($str, $encoding ?? Yii::$app->charset);
}

// TODO 下载表情图片
function emotion($message = '', $size = '24px')
{
    $emotions = [
        "/::)",
        "/::~",
        "/::B",
        "/::|",
        "/:8-)",
        "/::<",
        "/::$",
        "/::X",
        "/::Z",
        "/::'(",
        "/::-|",
        "/::@",
        "/::P",
        "/::D",
        "/::O",
        "/::(",
        "/::+",
        "/:--b",
        "/::Q",
        "/::T",
        "/:,@P",
        "/:,@-D",
        "/::d",
        "/:,@o",
        "/::g",
        "/:|-)",
        "/::!",
        "/::L",
        "/::>",
        "/::,@",
        "/:,@f",
        "/::-S",
        "/:?",
        "/:,@x",
        "/:,@@",
        "/::8",
        "/:,@!",
        "/:!!!",
        "/:xx",
        "/:bye",
        "/:wipe",
        "/:dig",
        "/:handclap",
        "/:&-(",
        "/:B-)",
        "/:<@",
        "/:@>",
        "/::-O",
        "/:>-|",
        "/:P-(",
        "/::'|",
        "/:X-)",
        "/::*",
        "/:@x",
        "/:8*",
        "/:pd",
        "/:<W>",
        "/:beer",
        "/:basketb",
        "/:oo",
        "/:coffee",
        "/:eat",
        "/:pig",
        "/:rose",
        "/:fade",
        "/:showlove",
        "/:heart",
        "/:break",
        "/:cake",
        "/:li",
        "/:bome",
        "/:kn",
        "/:footb",
        "/:ladybug",
        "/:shit",
        "/:moon",
        "/:sun",
        "/:gift",
        "/:hug",
        "/:strong",
        "/:weak",
        "/:share",
        "/:v",
        "/:@)",
        "/:jj",
        "/:@@",
        "/:bad",
        "/:lvu",
        "/:no",
        "/:ok",
        "/:love",
        "/:<L>",
        "/:jump",
        "/:shake",
        "/:<O>",
        "/:circle",
        "/:kotow",
        "/:turn",
        "/:skip",
        "/:oY",
        "/:#-0",
        "/:hiphot",
        "/:kiss",
        "/:<&",
        "/:&>",
    ];
    foreach ($emotions as $index => $emotion) {
        $message = str_replace($emotion,
            '<img style="width:' . $size . ';vertical-align:middle;" src="http://res.mail.qq.com/zh_CN/images/mo/DEFAULT2/' . $index . '.gif" />',
            $message);
    }

    return $message;
}

// TODO
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key != '' ? $key : $GLOBALS['_W']['config']['setting']['authkey']);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()),
        -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d',
            $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = [];
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10,
                16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }

}

function sizecount($size)
{
    $units = array('Bytes', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($size, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow));

    return round($bytes, 2) . ' ' . $units[$pow];
}

function bytecount($str)
{
    $unit = strtoupper($str[strlen($str) - 1]);
    if ($unit == 'B') {
        $str = substr($str, 0, -1);
    } elseif ($unit == 'K') {
        return floatval($str) * 1024;
    } elseif ($unit == 'M') {
        return floatval($str) * 1048576;
    } elseif ($unit == 'G') {
        return floatval($str) * 1073741824;
    } elseif ($unit == 'T') {
        return floatval($str) * 1073741824;
    }
}

function array2xml($arr, $level = 1)
{
    $s = $level == 1 ? "<xml>" : '';
    foreach ($arr as $tagname => $value) {
        if (is_numeric($tagname)) {
            $tagname = $value['TagName'];
            unset($value['TagName']);
        }
        if ( ! is_array($value)) {
            $s .= "<{$tagname}>" . (! is_numeric($value) ? '<![CDATA[' : '') . $value . (! is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
        } else {
            $s .= "<{$tagname}>" . array2xml($value, $level + 1) . "</{$tagname}>";
        }
    }
    $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);

    return $level == 1 ? $s . "</xml>" : $s;
}

function xml2array($xml)
{
    if (empty($xml)) {
        return [];
    }
    $result = [];
    $xmlobj = isimplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($xmlobj instanceof SimpleXMLElement) {
        $result = json_decode(json_encode($xmlobj), true);
        if (is_array($result)) {
            return $result;
        } else {
            return '';
        }
    } else {
        return $result;
    }
}

function scriptname()
{
    global $_W;
    $_W['script_name'] = basename($_SERVER['SCRIPT_FILENAME']);
    if (basename($_SERVER['SCRIPT_NAME']) === $_W['script_name']) {
        $_W['script_name'] = $_SERVER['SCRIPT_NAME'];
    } else {
        if (basename($_SERVER['PHP_SELF']) === $_W['script_name']) {
            $_W['script_name'] = $_SERVER['PHP_SELF'];
        } else {
            if (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $_W['script_name']) {
                $_W['script_name'] = $_SERVER['ORIG_SCRIPT_NAME'];
            } else {
                if (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
                    $_W['script_name'] = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $_W['script_name'];
                } else {
                    if (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],
                            $_SERVER['DOCUMENT_ROOT']) === 0) {
                        $_W['script_name'] = str_replace('\\', '/',
                            str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
                    } else {
                        $_W['script_name'] = 'unknown';
                    }
                }
            }
        }
    }

    return $_W['script_name'];
}

function utf8_bytes($cp)
{
    if ($cp > 0x10000) {
        return chr(0xF0 | (($cp & 0x1C0000) >> 18)) .
               chr(0x80 | (($cp & 0x3F000) >> 12)) .
               chr(0x80 | (($cp & 0xFC0) >> 6)) .
               chr(0x80 | ($cp & 0x3F));
    } elseif ($cp > 0x800) {
        return chr(0xE0 | (($cp & 0xF000) >> 12)) .
               chr(0x80 | (($cp & 0xFC0) >> 6)) .
               chr(0x80 | ($cp & 0x3F));
    } elseif ($cp > 0x80) {
        return chr(0xC0 | (($cp & 0x7C0) >> 6)) .
               chr(0x80 | ($cp & 0x3F));
    } else {
        return chr($cp);
    }
}

function media2local($media_id, $all = false)
{
    global $_W;
    load()->model('material');
    $data = material_get($media_id);
    if ( ! is_error($data)) {
        $data['attachment'] = tomedia($data['attachment'], true);
        if ( ! $all) {
            return $data['attachment'];
        }

        return $data;
    } else {
        return '';
    }
}

function aes_decode($message, $encodingaeskey = '', $appid = '')
{
    $key = base64_decode($encodingaeskey . '=');

    $ciphertext_dec = base64_decode($message);
    $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
    $iv = substr($key, 0, 16);

    mcrypt_generic_init($module, $key, $iv);
    $decrypted = mdecrypt_generic($module, $ciphertext_dec);
    mcrypt_generic_deinit($module);
    mcrypt_module_close($module);
    $block_size = 32;

    $pad = ord(substr($decrypted, -1));
    if ($pad < 1 || $pad > 32) {
        $pad = 0;
    }
    $result = substr($decrypted, 0, (strlen($decrypted) - $pad));
    if (strlen($result) < 16) {
        return '';
    }
    $content = substr($result, 16, strlen($result));
    $len_list = unpack("N", substr($content, 0, 4));
    $contentlen = $len_list[1];
    $content = substr($content, 4, $contentlen);
    $from_appid = substr($content, $xml_len + 4);
    if ( ! empty($appid) && $appid != $from_appid) {
        return '';
    }

    return $content;
}

function aes_encode($message, $encodingaeskey = '', $appid = '')
{
    $key = base64_decode($encodingaeskey . '=');
    $text = random(16) . pack("N", strlen($message)) . $message . $appid;

    $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
    $iv = substr($key, 0, 16);

    $block_size = 32;
    $text_length = strlen($text);
    $amount_to_pad = $block_size - ($text_length % $block_size);
    if ($amount_to_pad == 0) {
        $amount_to_pad = $block_size;
    }
    $pad_chr = chr($amount_to_pad);
    $tmp = '';
    for ($index = 0; $index < $amount_to_pad; $index++) {
        $tmp .= $pad_chr;
    }
    $text = $text . $tmp;
    mcrypt_generic_init($module, $key, $iv);
    $encrypted = mcrypt_generic($module, $text);
    mcrypt_generic_deinit($module);
    mcrypt_module_close($module);
    $encrypt_msg = base64_encode($encrypted);

    return $encrypt_msg;
}

function aes_pkcs7_decode($encrypt_data, $key, $iv = false)
{
    load()->library('pkcs7');
    $encrypt_data = base64_decode($encrypt_data);
    if ( ! empty($iv)) {
        $iv = base64_decode($iv);
    }
    $pc = new Prpcrypt($key);
    $result = $pc->decrypt($encrypt_data, $iv);
    if ($result[0] != 0) {
        return error($result[0], '解密失败');
    }

    return $result[1];
}

function isimplexml_load_string($string, $class_name = 'SimpleXMLElement', $options = 0, $ns = '', $is_prefix = false)
{
    libxml_disable_entity_loader(true);
    if (preg_match('/(\<\!DOCTYPE|\<\!ENTITY)/i', $string)) {
        return false;
    }

    return simplexml_load_string($string, $class_name, $options, $ns, $is_prefix);
}

function ihtml_entity_decode($str)
{
    $str = str_replace('&nbsp;', '#nbsp;', $str);

    return str_replace('#nbsp;', '&nbsp;', html_entity_decode(urldecode($str)));
}

function iarray_change_key_case($array, $case = CASE_LOWER)
{
    if ( ! is_array($array) || empty($array)) {
        return [];
    }
    $array = array_change_key_case($array, $case);
    foreach ($array as $key => $value) {
        if (empty($value) && is_array($value)) {
            $array[$key] = '';
        }
        if ( ! empty($value) && is_array($value)) {
            $array[$key] = iarray_change_key_case($value, $case);
        }
    }

    return $array;
}

function strip_gpc($values, $type = 'g')
{
    $filter = [
        'g' => "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
        'p' => "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
        'c' => "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
    ];
    if ( ! isset($values)) {
        return '';
    }
    if (is_array($values)) {
        foreach ($values as $key => $val) {
            $values[addslashes($key)] = strip_gpc($val, $type);
        }
    } else {
        if (preg_match("/" . $filter[$type] . "/is", $values, $match) == 1) {
            $values = '';
        }
    }

    return $values;
}

function parse_path($path)
{
    $danger_char = ['../', '{php', '<?php', '<%', '<?', '..\\', '\\\\', '\\', '..\\\\', '%00', '\0', '\r'];
    foreach ($danger_char as $char) {
        if (strexists($path, $char)) {
            return false;
        }
    }

    return $path;
}

function dir_size($dir)
{
    $size = 0;
    if (is_dir($dir)) {
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                if (is_dir("{$dir}/{$entry}")) {
                    $size += dir_size("{$dir}/{$entry}");
                } else {
                    $size += filesize("{$dir}/{$entry}");
                }
            }
        }
        closedir($handle);
    }

    return $size;
}

function get_first_pinyin($str)
{
    static $pinyin;
    $first_char = '';
    $str = trim($str);
    if (empty($str)) {
        return $first_char;
    }
    if (empty($pinyin)) {
        load()->library('pinyin');
        $pinyin = new Pinyin_Pinyin();
    }
    $first_char = $pinyin->get_first_char($str);

    return $first_char;
}

function strip_emoji($nickname)
{
    $clean_text = "";
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $nickname);
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regexMisc, '', $clean_text);
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);

    $clean_text = str_replace("'", '', $clean_text);
    $clean_text = str_replace('"', '', $clean_text);
    $clean_text = str_replace('“', '', $clean_text);
    $clean_text = str_replace('゛', '', $clean_text);
    $search = [" ", "　", "\n", "\r", "\t"];
    $replace = ["", "", "", "", ""];

    return str_replace($search, $replace, $clean_text);
}

function emoji_unicode_decode($string)
{
    preg_match_all('/\[U\+(\\w{4,})\]/i', $string, $match);
    if ( ! empty($match[1])) {
        foreach ($match[1] as $emojiUSB) {
            $string = str_ireplace("[U+{$emojiUSB}]", utf8_bytes(hexdec($emojiUSB)), $string);
        }
    }

    return $string;
}

function emoji_unicode_encode($string)
{
    $ranges = [
        '\\\\ud83c[\\\\udf00-\\\\udfff]',
        '\\\\ud83d[\\\\udc00-\\\\ude4f]',
        '\\\\ud83d[\\\\ude80-\\\\udeff]',
    ];
    preg_match_all('/' . implode('|', $ranges) . '/i', $string, $match);
    print_r($match);
    exit;
}

function getglobal($key)
{
    global $_W;
    $key = explode('/', $key);

    $v = &$_W;
    foreach ($key as $k) {
        if ( ! isset($v[$k])) {
            return null;
        }
        $v = &$v[$k];
    }

    return $v;
}

if ( ! function_exists('starts_with')) {
    function starts_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }
}

function check_url_not_outside_link($redirect)
{
    global $_W;
    if (starts_with($redirect, 'http') && ! starts_with($redirect, $_W['siteroot'])) {
        $redirect = $_W['siteroot'];
    }

    return $redirect;
}

function remove_xss($val)
{
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val);
        $val = preg_replace('/(�{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val);
    }
    $ra1 = [
        'javascript',
        'vbscript',
        'expression',
        'applet',
        'meta',
        'xml',
        'blink',
        'link',
        'script',
        'embed',
        'object',
        'frameset',
        'ilayer',
        'bgsound',
        'title',
        'base',
    ];
    $ra2 = [
        'onabort',
        'onactivate',
        'onafterprint',
        'onafterupdate',
        'onbeforeactivate',
        'onbeforecopy',
        'onbeforecut',
        'onbeforedeactivate',
        'onbeforeeditfocus',
        'onbeforepaste',
        'onbeforeprint',
        'onbeforeunload',
        'onbeforeupdate',
        'onblur',
        'onbounce',
        'oncellchange',
        'onchange',
        'onclick',
        'oncontextmenu',
        'oncontrolselect',
        'oncopy',
        'oncut',
        'ondataavailable',
        'ondatasetchanged',
        'ondatasetcomplete',
        'ondblclick',
        'ondeactivate',
        'ondrag',
        'ondragend',
        'ondragenter',
        'ondragleave',
        'ondragover',
        'ondragstart',
        'ondrop',
        'onerror',
        'onerrorupdate',
        'onfilterchange',
        'onfinish',
        'onfocus',
        'onfocusin',
        'onfocusout',
        'onhelp',
        'onkeydown',
        'onkeypress',
        'onkeyup',
        'onlayoutcomplete',
        'onload',
        'onlosecapture',
        'onmousedown',
        'onmouseenter',
        'onmouseleave',
        'onmousemove',
        'onmouseout',
        'onmouseover',
        'onmouseup',
        'onmousewheel',
        'onmove',
        'onmoveend',
        'onmovestart',
        'onpaste',
        'onpropertychange',
        'onreadystatechange',
        'onreset',
        'onresize',
        'onresizeend',
        'onresizestart',
        'onrowenter',
        'onrowexit',
        'onrowsdelete',
        'onrowsinserted',
        'onscroll',
        'onselect',
        'onselectionchange',
        'onselectstart',
        'onstart',
        'onstop',
        'onsubmit',
        'onunload',
        'import',
    ];
    $ra = array_merge($ra1, $ra2);
    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(�{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {
                $found = false;
            }
        }
    }

    return $val;
}

/**
 * 执行sql语句
 *
 * @param $sql
 * @param array $params
 *
 * @return int
 * @throws \yii\db\Exception
 */
function pdo_query($sql, $params = [])
{
    SqlParser::checkQuery($sql);

    return Yii::$app->db->createCommand($sql, $params)->execute();
}

/**
 * 获取指定列数据
 *
 * @param string $sql
 * @param array $params
 * @param int $column
 *
 * @return array
 * @throws \yii\db\Exception
 */
function pdo_fetchcolumn($sql, $params = [], int $column = 0)
{
    SqlParser::checkQuery($sql);
    $command = Yii::$app->db->createCommand($sql, $params);
    if ($column === 0) {
        return $command->queryColumn();
    } else {
        $result = $command->queryAll(\PDO::FETCH_NUM);

        return ArrayHelper::getColumn($result, $column);
    }
}

/**
 * 获取一行数据
 *
 * @param string $sql
 * @param array $params
 *
 * @return array|false
 * @throws \yii\db\Exception
 */
function pdo_fetch($sql, $params = [])
{
    SqlParser::checkQuery($sql);

    return Yii::$app->db->createCommand($sql, $params)->queryOne();
}

/**
 * 获取多行数据
 *
 * @param string $sql
 * @param array $params
 * @param string|null $keyField
 *
 * @return array
 * @throws \yii\db\Exception
 */
function pdo_fetchall($sql, $params = [], string $keyField = null)
{
    SqlParser::checkQuery($sql);
    $result = Yii::$app->db->createCommand($sql, $params)->queryAll();

    return ArrayHelper::getColumn($result, $keyField);
}

/**
 * 获取一行数据(自动拼装Sql)
 *
 * @param string $tablename
 * @param array $params
 * @param array $fields
 * @param array $orderby
 *
 * @return array|false
 * @throws \yii\db\Exception
 */
function pdo_get($tablename, $params = [], $fields = [], $orderBy = [])
{
    $select = SqlPaser::parseSelect($fields);
    $condition = SqlPaser::parseParameter($params, 'AND');
    $orderBySql = SqlPaser::parseOrderby($orderBy);

    $sql = "{$select} FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . " $orderBySql LIMIT 1";

    return pdo_fetch($sql, $condition['params']);
}

/**
 * 获取多行数据(自动拼装Sql)
 *
 * @param string $tablename
 * @param array $params
 * @param array $fields
 * @param string $keyField
 * @param array $orderBy
 * @param array $limit
 *
 * @return array
 * @throws \yii\db\Exception
 */
function pdo_getall(
    $tablename,
    $params = [],
    $fields = [],
    $keyField = '',
    $orderBy = [],
    $limit = []
) {
    $select = SqlPaser::parseSelect($fields);
    $condition = SqlPaser::parseParameter($params, 'AND');

    $limitSql = SqlPaser::parseLimit($limit);
    $orderBySql = SqlPaser::parseOrderby($orderBy);

    $sql = "{$select} FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . $orderBySql . $limitSql;

    return pdo_fetchall($sql, $condition['params'], $keyField);
}

/**
 * 获取指定列的数据(自动拼装Sql)
 *
 * @param string $tablename
 * @param array $params
 * @param array $limit
 * @param null $total
 * @param array $fields
 * @param string $keyField
 * @param array $orderBy
 *
 * @return array
 * @throws \yii\db\Exception
 */
function pdo_getslice(
    $tablename,
    $params = [],
    $limit = [],
    &$total = null,
    $fields = [],
    $keyField = '',
    $orderBy = []
) {
    $select = SqlPaser::parseSelect($fields);
    $condition = SqlPaser::parseParameter($params, 'AND');
    $limitSql = SqlPaser::parseLimit($limit);

    if ( ! empty($orderby)) {
        if (is_array($orderby)) {
            $orderBySql = implode(',', $orderBy);
        } else {
            $orderBySql = $orderBy;
        }
    }
    $sql = "{$select} FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . (! empty($orderBySql) ? " ORDER BY $orderBySql " : '') . $limitSql;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : ''),
        $condition['params']);

    return pdo_fetchall($sql, $condition['params'], $keyField);
}

/**
 * 获取指定列的数据(自动拼装Sql)
 *
 * @param $tablename
 * @param array $params
 * @param $field
 *
 * @return bool|mixed
 * @throws \yii\db\Exception
 */
function pdo_getcolumn($tablename, $params = [], $field)
{
    $result = pdo_get($tablename, $params, $field);
    if ( ! empty($result)) {
        if (strexists($field, '(')) {
            return array_shift($result);
        } else {
            return $result[$field];
        }
    }

    return false;
}

/**
 * 查询指定条件的数据是否存在(自动拼装Sql)
 *
 * @param string $tablename
 * @param array $params
 *
 * @return bool
 * @throws \yii\db\Exception
 */
function pdo_exists($tablename, $params = [])
{
    $row = pdo_get($tablename, $params);
    if (empty($row) || ! is_array($row) || count($row) == 0) {
        return false;
    }

    return true;
}

/**
 * 查询指定条件的数据统计数(自动拼装Sql)
 *
 * @param string $tablename
 * @param array $params
 * @param int $cachetime
 *
 * @return int
 * @throws \yii\db\Exception
 */
function pdo_count($tablename, $params = [], $cachetime = 15)
{
    // TODO cache
    return (int)pdo_getcolumn($tablename, $params, 'count(*)');
}

/**
 * 更新数据(自动拼装Sql)
 *
 * @param string $table
 * @param array $data
 * @param array $params
 * @param string $glue
 *
 * @return int
 * @throws \yii\db\Exception
 */
function pdo_update($table, $data = [], $params = [], $glue = 'AND')
{
    $fields = SqlPaser::parseParameter($data, ',');
    $condition = SqlPaser::parseParameter($params, $glue);
    $params = array_merge($fields['params'], $condition['params']);
    $sql = "UPDATE " . tablename($table) . " SET {$fields['fields']}";
    $sql .= $condition['fields'] ? ' WHERE ' . $condition['fields'] : '';

    return pdo_query($sql, $params);
}

/**
 * 插入数据(自动拼装Sql)
 *
 * @param $table
 * @param array $data
 * @param bool $replace
 *
 * @return int
 * @throws \yii\db\Exception
 */
function pdo_insert($table, $data = [], $replace = false)
{
    $cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
    $condition = SqlPaser::parseParameter($data, ',');

    return pdo_query("$cmd " . tablename($table) . " SET {$condition['fields']}", $condition['params']);
}

/**
 * 删除数据(自动拼装Sql)
 *
 * @param string $table
 * @param array $params
 * @param string $glue
 *
 * @return int
 * @throws \yii\db\Exception
 */
function pdo_delete($table, $params = [], $glue = 'AND')
{
    $condition = SqlPaser::parseParameter($params, $glue);
    $sql = "DELETE FROM " . tablename($table);
    $sql .= $condition['fields'] ? ' WHERE ' . $condition['fields'] : '';

    return pdo_query($sql, $condition['params']);
}

/**
 * 获取最后插入的数据ID
 *
 * @return string
 */
function pdo_insertid()
{
    return Yii::$app->db->getLastInsertID();
}

/**
 * 开始事务
 *
 * @return \yii\db\Transaction
 */
function pdo_begin()
{
    Yii::$app->db->beginTransaction();
}

/**
 * 事务提交
 *
 * @throws \yii\db\Exception
 */
function pdo_commit()
{
    Yii::$app->db->getTransaction()->commit();
}

/**
 * 事务回滚
 */
function pdo_rollback()
{
    Yii::$app->db->getTransaction()->rollBack();
}

function pdo_debug($output = true, $append = [])
{
    throw new UnsupportedException();
}

/**
 * 批量运行sql语句
 *
 * @param string $sql
 */
function pdo_run($sql)
{
    $db = Yii::$app->db;
    // @see https://stackoverflow.com/questions/7690380/regular-expression-to-match-all-comments-in-a-t-sql-script/13821950#13821950 移除注释
    $sql = preg_replace('@(([\'"]).*?[^\\\]\2)|((?:\#|--).*?$|/\*(?:[^/*]|/(?!\*)|\*(?!/)|(?R))*\*\/)\s*|(?<=;)\s+@ms',
        '$1', $sql);
    // 替换前缀
    $sql = str_replace(' ims_', ' ' . $db->tablePrefix, $sql);
    $sql = str_replace(' `ims_', ' `' . $db->tablePrefix, $sql);

    foreach (explode(';', $sql) as $sql) {
        if ( ! empty($sql)) {
            pdo_query($sql);
        }
    }
}

/**
 * 查询自乱是否存在
 *
 * @param string $tablename
 * @param string $fieldName
 *
 * @return bool
 */
function pdo_fieldexists($tablename, $fieldName = '')
{
    return Yii::$app->db->getSchema()->getTableSchema(pdo_tablename($tablename))->getColumn($fieldName) !== null;
}

/**
 * 匹配表的字段类型和长度
 *
 * @param string $tablename
 * @param string $fieldName
 * @param string $dataType
 * @param string|int $length
 *
 * @return bool
 */
function pdo_fieldmatch($tablename, $fieldName, $dataType = '', $length = '')
{
    $column = Yii::$app->db->getTableSchema(pdo_tablename($tablename))->getColumn($fieldName);

    if ($column !== null) {
        if ( ! empty($datatype)) {
            $dataType .= ! empty($length) ? '(' . $length . ')' : '';

            return stripos($column->dbType, $dataType) === 0;
        }

        return true;
    }

    return false;
}

/**
 * 查询表索引名是否存在
 *
 * @param string $tablename
 * @param string $indexName
 */
function pdo_indexexists($tablename, $indexName = '')
{
    $indexes = ArrayHelper::getColumn(Yii::$app->db->getSchema()->getTableIndexes(pdo_tablename($tablename)), 'name');

    return in_array($indexName, $indexes);
}

/**
 * 获取表字段名
 *
 * @param string $tablename
 *
 * @return array
 */
function pdo_fetchallfields($tablename)
{
    return Yii::$app->db->getTableSchema(pdo_tablename($tablename))->columnNames;
}

/**
 * 查询表是否存在
 *
 * @param $tablename
 *
 * @return bool
 */
function pdo_tableexists($tablename)
{
    return Yii::$app->db->getTableSchema(pdo_tablename($tablename)) !== null;
}

/**
 * 返回带前缀的表名
 *
 * @param $tablename
 *
 * @return string
 */
function pdo_tablename($tablename)
{
    $prefix = Yii::$app->db->tablePrefix;

    return strpos($tablename, $prefix) === 0 ? $tablename : $prefix . $tablename;
}