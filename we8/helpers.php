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
//function isetcookie($key, $value, $expire = 0, $httponly = false)
//{
//}

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

/**
 * 获取指定关键字的键值数组
 *
 * @param $keys
 * @param $src
 * @param bool $default
 *
 * @return array
 */
function array_elements($keys, $src, $default = false)
{
    if ( ! is_array($keys)) {
        $keys = [$keys];
    }

    $return = [];

    foreach ($keys as $key) {
        $return[$key] = isset($src[$key]) ? $src[$key] : $default;
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
    }

    return ! $limit ? true : $num;
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

/**
 * 拼装web url
 *
 * @param string $segment
 * @param array $params
 *
 * @return string
 */
function wurl($segment, $params = [])
{
    $params[0] = '/web/' . $segment;

    return yii\helpers\Url::to($params);
}

/**
 * 拼装app url
 *
 * @param string $segment
 * @param array $params
 * @param bool $noRedirect
 * @param bool $addHost
 *
 * @return string
 */
function murl($segment, $params = [], $noRedirect = true, $schema = false)
{
    $params[0] = '/app/' . $segment;
    $url = yii\helpers\Url::to($params, $schema);
    if ($noRedirect) {
        $url .= '&wxref=mp.weixin.qq.com#wechat_redirect';
    }

    return $url;
}

/**
 * 组合凭借parse_url后的url
 *
 * @param array $parsed_url
 *
 * @return string
 */
function build_parsed_url($parsed_url)
{
    $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
    $pass = ($user || $pass) ? "$pass@" : '';
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query = isset($parsed_url['query']) ? '?' . (is_array($parsed_url['query']) ? http_build_query($parsed_url['query']) : $parsed_url['query']) : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

    return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
}

// TODO
function pagination(
    $total,
    $pageIndex,
    $pageSize = 15,
    $url = '',
    array $context = []
) {
    $pdata = array_merge([
        'tcount'  => 0,
        'tpage'   => 0,
        'cindex'  => 0,
        'findex'  => 0,
        'pindex'  => 0,
        'nindex'  => 0,
        'lindex'  => 0,
        'options' => '',
    ], [
        'tcount' => $total,
        'tpage'  => (empty($pageSize) || $pageSize < 0) ? 1 : ceil($total / $pageSize),
    ]);

    if ($pdata['tpage'] <= 1) {
        return '';
    }

    $cindex = $pageIndex;
    $pdata['cindex'] = $cindex;
    $pdata['findex'] = 1;
    $pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
    $pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
    $pdata['lindex'] = $pdata['tpage'];

    $context = array_merge([
        'before'           => 5,
        'after'            => 4,
        'ajaxcallback'     => null,
        'callbackfuncname' => null,
        'isajax'           => false,
    ], $context);

    if ($context['ajaxcallback']) {
        $context['isajax'] = true;
    }
    $callbackfunc = $context['callbackfuncname'];

    $schemas = parse_url(Yii::$app->request->getUrl());
    $schemas['query'] = empty($schemas['query']) ? [] : parse_str($schemas['query']);

    // todo deprecate ng-click
    $tpl = 'href="javascript:;" page="{index}" ' . ($callbackfunc ? 'ng-click="{callback}(\'{url}\', \'{index}\', this);"' : '');

    if ($context['isajax']) {
        if (empty($url)) {
            $url = Yii::$app->request->getUrl();
        }

        $pdata['faa'] = strtr($tpl, ['{index}' => $pdata['findex'], '{callback}' => $callbackfunc, '{url}' => $url]);
        $pdata['paa'] = strtr($tpl, ['{index}' => $pdata['pindex'], '{callback}' => $callbackfunc, '{url}' => $url]);
        $pdata['naa'] = strtr($tpl, ['{index}' => $pdata['nindex'], '{callback}' => $callbackfunc, '{url}' => $url]);
        $pdata['laa'] = strtr($tpl, ['{index}' => $pdata['lindex'], '{callback}' => $callbackfunc, '{url}' => $url]);
    } else {
        if ( ! empty($url)) {
            $pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
            $pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
            $pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
            $pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
        } else {
            $schemas['query']['page'] = $pdata['findex'];
            $pdata['faa'] = 'href="' . build_parsed_url($schemas) . '"';
            $schemas['query']['page'] = $pdata['pindex'];
            $pdata['paa'] = 'href="' . build_parsed_url($schemas) . '"';
            $schemas['query']['page'] = $pdata['nindex'];
            $pdata['naa'] = 'href="' . build_parsed_url($schemas) . '"';
            $schemas['query']['page'] = $pdata['lindex'];
            $pdata['laa'] = 'href="' . build_parsed_url($schemas) . '"';
        }
    }

    $html = '<div><ul class="pagination pagination-centered">';
    $html .= "<li><a {$pdata['faa']} class=\"pager-nav\">首页</a></li>";

    if (empty($callbackfunc)) {
        $html .= "<li><a {$pdata['paa']} class=\"pager-nav\">&laquo;上一页</a></li>";
    }

    if ( ! $context['before'] && $context['before'] != 0) {
        $context['before'] = 5;
    }
    if ( ! $context['after'] && $context['after'] != 0) {
        $context['after'] = 4;
    }

    if ($context['after'] && $context['before']) {
        $range = [];
        $range['start'] = max(1, $pdata['cindex'] - $context['before']);
        $range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
        if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
            $range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
            $range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
        }
        for ($i = $range['start']; $i <= $range['end']; $i++) {
            if ($context['isajax']) {
                $aa = strtr($tpl, ['{index}' => $i, '{callback}' => $callbackfunc, '{url}' => $url]);
            } else {
                if ($url) {
                    $aa = 'href="?' . str_replace('*', $i, $url) . '"';
                } else {
                    $schemas['query']['page'] = $i;
                    $aa = 'href="' . build_parsed_url($schemas) . '"';
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

/**
 * 获取上一个请求连接
 *
 * @param string $default
 *
 * @return string
 */
function referer($default = '')
{
    $request = Yii::$app->request;

    $referer = $request->get('referer') ?? $request->getReferrer();
    $referer = str_replace('&amp;', '&', $referer);

    return strip_tags($referer);
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
 * 删除多余字符
 *
 * @param $string
 * @param $length
 * @param bool $dot 为true则在字符末尾补充...字符串
 * @param string $charset
 *
 * @return mixed|string
 */
function cutstr($string, $length, $dot = false, $charset = null)
{
    $charset = strtolower($charset) == 'gbk' ? 'gbk' : 'utf8';

    if (istrlen($string, $charset) > $length) {
        $string = mb_substr($string, 0, $length, $charset);
        if ($dot) {
            $string = $string . "...";
        }
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

// TODO
//function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
//{
//}

/**
 * 返回可读存储位
 *
 * @param $size
 *
 * @return string
 */
function sizecount($size)
{
    $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($size, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow));

    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * 可读存储位转Bytes
 *
 * @param $string
 *
 * @return float|int
 */
function bytecount($string)
{
    if (strtoupper($string[strlen($string) - 1]) == 'B') {
        $str = substr($string, 0, -1);
    }

    $unit = strtoupper($str[strlen($str) - 1]);
    if ($unit == 'K') {
        return floatval($str) * 1024;
    } elseif ($unit == 'M') {
        return floatval($str) * 1048576;
    } elseif ($unit == 'G') {
        return floatval($str) * 1073741824;
    } elseif ($unit == 'T') {
        return floatval($str) * 1099511627776;
    }

    return $string;
}

/**
 * 数组转xml
 *
 * @param array $arr
 * @param int $level
 *
 * @return string|string[]|null
 */
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

/**
 * xml转数组
 *
 * @param $xml
 *
 * @return mixed
 */
function xml2array($xml)
{
    $result = [];
    if ( ! empty($xml)) {
        $xmlObj = isimplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xmlObj instanceof SimpleXMLElement) {
            $result = json_decode(json_encode($xmlObj), true);
            if ( ! is_array($result)) {
                return '';
            }
        }
    }

    return $result;
}

/**
 * 是否简单xml类型(如果是则自定返回SimpleXMLElement实例)
 *
 * @param $string
 * @param string $className
 * @param int $options
 * @param string $ns
 * @param bool $isPrefix
 *
 * @return bool|SimpleXMLElement
 */
function isimplexml_load_string($string, $className = 'SimpleXMLElement', $options = 0, $ns = '', $isPrefix = false)
{
    libxml_disable_entity_loader(true);
    if ( ! preg_match('/(\<\!DOCTYPE|\<\!ENTITY)/i', $string)) {
        $string = preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f\\x7f]/", '', $string);

        return simplexml_load_string($string, $className, $options, $ns, $isPrefix);
    }

    return false;
}

/**
 * 获取当前执行PHP文件名
 *
 * @return string
 */
function scriptname()
{
    return basename(Yii::$app->request->scriptFile);
}

/**
 * 转换成utf8格式字符
 *
 * @param $cp
 *
 * @return string
 */
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

//// TODO
//function media2local($media_id, $all = false)
//{
//}

// TODO
//function aes_decode($message, $encodingaeskey = '', $appid = '')
//{
//}

// TODO
//function aes_encode($message, $encodingaeskey = '', $appid = '')
//{
//
//}

// TODO
//function aes_pkcs7_decode($encrypt_data, $key, $iv = false)
//{
//
//}

/**
 * 转换特殊字符(保留&nbsp;空格字符)
 *
 * @param $str
 *
 * @return mixed
 */
function ihtml_entity_decode($str)
{
    $str = str_replace('&nbsp;', '!nbsp;', $str);
    $str = html_entity_decode(urldecode($str));

    return str_replace('!nbsp;', '&nbsp;', $str);
}

/**
 * 转换数组关键字大小写
 *
 * @param $array
 * @param int $case
 *
 * @return array
 */
function iarray_change_key_case($array, $case = CASE_LOWER)
{
    if (is_array($array) && ! empty($array)) {
        $array = array_change_key_case($array, $case);
        foreach ($array as $key => $value) {
            if (empty($value) && is_array($value)) {
                $array[$key] = '';
            }
            if ( ! empty($value) && is_array($value)) {
                $array[$key] = iarray_change_key_case($value, $case);
            }
        }
    } else {
        $array = [];
    }

    return $array;
}

/**
 * 安全检测并过滤$_GET, $_POST, $_COOKIE关键字
 *
 * @param $values
 * @param string $type
 *
 * @return array|string
 */
function strip_gpc($values, $type = 'g')
{
    if ( ! empty($values)) {
        if (is_array($values)) {
            foreach ($values as $key => $val) {
                $values[addslashes($key)] = strip_gpc($val, $type);
            }
        } else {
            $filters = [
                'g' => "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
                'p' => "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
                'c' => "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
            ];
            if (preg_match('/' . $filters[$type] . '/is', $values, $match) == 1) {
                $values = '';
            }
        }
    } else {
        $values = '';
    }

    return $values;
}

/**
 * 安全检测路径字符串
 *
 * @param $path
 *
 * @return bool
 */
function parse_path($path)
{
    $characters = ['../', '..\\', '..\\\\', '\\\\', '\\', '{php', '<?php', '<%', '<?', '%00', '\0', '\r'];
    foreach ($characters as $char) {
        if (strexists($path, $char)) {
            return false;
        }
    }

    return $path;
}

/**
 * 获取目录的size
 *
 * @param $dir
 *
 * @return int
 */
function dir_size($dir)
{
    // 更简洁更精确但是性能差
//    $ite = new RecursiveDirectoryIterator($dir);
//    $bytesTotal=0;
//    foreach (new RecursiveIteratorIterator($ite) as $filename => $cur) {
//        $bytesTotal += $cur->getSize();
//    }
//    return $bytesTotal;

    $size = 0;
    if (is_dir($dir)) {
        $handle = opendir($dir);
        while (($entry = readdir($handle)) !== false) {
            if ( ! in_array($entry, ['.', '..'])) {
                $subDir = $dir . DIRECTORY_SEPARATOR . $entry;
                $size += is_dir($subDir) ? dir_size($subDir) : filesize($subDir);
            }

        }
        closedir($handle);
    }

    return $size;
}

/**
 * 获取字符串的第一个拼音字符
 *
 * @param $string
 *
 * @return string
 */
function get_first_pinyin($string)
{
    return Yii::$app->pinyin->firstChar($string);
}

/**
 * 移除字符串中的emoji字符
 *
 * @param $string
 *
 * @return mixed
 */
function strip_emoji($string)
{
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $cleanText = preg_replace($regexEmoticons, '', $string);

    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $cleanText = preg_replace($regexSymbols, '', $cleanText);

    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $cleanText = preg_replace($regexTransport, '', $cleanText);

    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $cleanText = preg_replace($regexMisc, '', $cleanText);

    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $cleanText = preg_replace($regexDingbats, '', $cleanText);

    // todo 需要清除多余字符???
    $search = ["'", '"', '“', '゛', " ", "　", "\n", "\r", "\t"];
    $replace = ['', '', '', '', '', '', '', '', ''];

    return str_replace($search, $replace, $cleanText);
}

/**
 * 替换emoji代码为emoji字符
 *
 * @param string $string
 *
 * @return string
 */
function emoji_unicode_decode($string)
{
    if (preg_match_all('/\[U\+(\\w{4,})\]/i', $string, $matches)) {
        foreach ($matches[1] as $eomjiCode) {
            $string = str_ireplace("[U+{$eomjiCode}]", utf8_bytes(hexdec($eomjiCode)), $string);
        }
    }

    return $string;
}

function emoji_unicode_encode($string)
{
    // TODO 转换emoji为emoji字符串
    throw new \weikit\core\exceptions\UnsupportedException('Emoji encode not is support yet');
}

/**
 * 递归查找$_W值
 *
 * @param $key
 *
 * @return mixed
 */
function getglobal($key)
{
    global $_W;

    return \yii\helpers\ArrayHelper::getValue($_W, str_replace('/', '.', $key));
}


if ( ! function_exists('starts_with')) {
    /**
     * 判断字符串是否指定字符开头
     *
     * @param string $haystack
     * @param array|string $needles
     *
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && mb_substr($haystack, 0, mb_strlen($needle)) === strval($needle)) {
                return true;
            }
        }

        return false;
    }
}

/**
 * 是否外部链接,如果是则返回站点链接
 *
 * @param $redirect
 *
 * @return mixed|string
 */
function check_url_not_outside_link($redirect)
{
    $baseUrl = Yii::$app->request->baseUrl;

    return starts_with($redirect, 'http') && !starts_with($redirect, $baseUrl) ? $baseUrl : $redirect;
}

/**
 * XSS过滤字符串
 *
 * @param $val
 *
 * @return string
 */
function remove_xss($val)
{
    return \yii\helpers\HtmlPurifier::process($val);
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