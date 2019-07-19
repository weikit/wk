<?php

namespace weikit\core\addon;

use Yii;
use DOMElement;
use DOMDocument;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\base\InvalidArgumentException;

/**
 * 扩展模块配置解析器
 * @package weikit\core\addon
 *
 * @property boolean $isValid
 * @property string $content
 * @property ModuleManifest $manifest
 */
class ModuleParser extends BaseObject
{
    /**
     * @var array
     */
    const MODULE_BINDINGS = [
        'cover'          => [
            'name'  => 'cover',
            'title' => '功能封面',
            'desc'  => '功能封面是定义微站里一个独立功能的入口(手机端操作), 将呈现为一个图文消息, 点击后进入微站系统中对应的功能.',
        ],
        'rule'           => [
            'name'  => 'rule',
            'title' => '规则列表',
            'desc'  => '规则列表是定义可重复使用或者可创建多次的活动的功能入口(管理后台Web操作), 每个活动对应一条规则. 一般呈现为图文消息, 点击后进入定义好的某次活动中.',
        ],
        'menu'           => [
            'name'  => 'menu',
            'title' => '管理中心导航菜单',
            'desc'  => '管理中心导航菜单将会在管理中心生成一个导航入口(管理后台Web操作), 用于对模块定义的内容进行管理.',
        ],
        'home'           => [
            'name'  => 'home',
            'title' => '微站首页导航图标',
            'desc'  => '在微站的首页上显示相关功能的链接入口(手机端操作), 一般用于通用功能的展示.',
        ],
        'profile'        => [
            'name'  => 'profile',
            'title' => '微站个人中心导航',
            'desc'  => '在微站的个人中心上显示相关功能的链接入口(手机端操作), 一般用于个人信息, 或针对个人的数据的展示.',
        ],
        'shortcut'       => [
            'name'  => 'shortcut',
            'title' => '微站快捷功能导航',
            'desc'  => '在微站的快捷菜单上展示相关功能的链接入口(手机端操作), 仅在支持快捷菜单的微站模块上有效.',
        ],
        'function'       => [
            'name'  => 'function',
            'title' => '微站独立功能',
            'desc'  => '需要特殊定义的操作, 一般用于将指定的操作指定为(direct). 如果一个操作没有在具体位置绑定, 但是需要定义为(direct: 直接访问), 可以使用这个嵌入点',
        ],
        'page'           => [
            'name'  => 'page',
            'title' => '小程序入口',
            'desc'  => '用于小程序入口的链接',
        ],
        'system_welcome' => [
            'name'  => 'system_welcome',
            'title' => '系统首页导航菜单',
            'desc'  => '系统首页导航菜单将会在管理中心生成一个导航入口, 用于对系统首页定义的内容进行管理.',
        ],
        'webapp'         => [
            'name'  => 'webapp',
            'title' => 'PC入口',
            'desc'  => '用于PC入口的链接',
        ],
        'phoneapp'       => [
            'name'  => 'phoneapp',
            'title' => 'APP入口',
            'desc'  => '用于APP入口的链接',
        ],
    ];
    /**
     * @var string
     */
    private $_content;
    /**
     * @var boolean
     */
    private $_isValid;
    /**
     * @var array|ModuleManifest
     */
    private $_manifest;

    /**
     * @return string
     */
    public function getContent()
    {
        if ($this->_content === null) {
            throw new InvalidArgumentException('Module xml content is empty');
        }
        return $this->_content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->_content = $content;
        $this->_isValid = null;
        $this->_manifest = null;
    }

    /**
     * @return bool
     */
    public function getIsValid()
    {
        if ($this->_isValid === null) {
            $this->_isValid = empty($this->_manifest);
        }

        return $this->_isValid;
    }

    /**
     * @return ModuleManifest|array
     */
    public function getManifest()
    {
        if ($this->_manifest === null) {
            $this->_manifest = $this->getIsValid() ? Yii::createObject([
                'class' => ModuleManifest::class,
                'data' => $this->parse(),
            ]) : [];
        }
        return $this->_manifest;
    }

    /**
     * 解析设置
     *
     * @return ModuleManifest|array
     */
    protected function parse()
    {
        $content = $this->getContent();

        if (stripos($content, '<manifest') === false) {
            return [];
        }

        $dom = new DOMDocument();
        $dom->loadXML($content);
        $root = $dom->getElementsByTagName('manifest')->item(0);
        if (empty($root)) {
            return [];
        }

        $application = $root->getElementsByTagName('application')->item(0);
        if (empty($application)) {
            return [];
        }

        $manifest = [
            'name'        => trim($this->getDomTextContent($application, 'identifie')),
            'title'       => trim($this->getDomTextContent($application, 'name')),
            'version'     => trim($this->getDomTextContent($application, 'version')),
            'type'        => trim($this->getDomTextContent($application, 'type')),
            'ability'     => trim($this->getDomTextContent($application, 'ability')),
            'author'      => trim($this->getDomTextContent($application, 'author')),
            'url'         => trim($this->getDomTextContent($application, 'url')),
            'description' => trim($this->getDomTextContent($application, 'description')),

            'setting'   => trim($application->getAttribute('setting')) == 'true',

            'subscribes'   => [],
            'handles'      => [],
            'isrulefields' => false,
            'iscard'       => false,
            'supports'     => [],
            'oauth_type'   => OAUTH_TYPE_BASE,

            'install'   => $this->getDomTextContent($root, 'install'),
            'uninstall' => $this->getDomTextContent($root, 'uninstall'),
            'upgrade'   => $this->getDomTextContent($root, 'upgrade'),

            'plugins'     => [],
            'bindings'    => [],
            'permissions' => [],
        ];

        $platform = $root->getElementsByTagName('platform')->item(0);
        if ( ! empty($platform)) {

            $subscribes = $platform->getElementsByTagName('subscribes')->item(0);
            if ( ! empty($subscribes)) {
                $messages = $subscribes->getElementsByTagName('message');
                for ($i = 0; $i < $messages->length; $i++) {
                    $t = $messages->item($i)->getAttribute('type');
                    if ( ! empty($t)) {
                        $manifest['subscribes'][] = $t;
                    }
                }
            }

            $handles = $platform->getElementsByTagName('handles')->item(0);
            if ( ! empty($handles)) {
                $messages = $handles->getElementsByTagName('message');
                for ($i = 0; $i < $messages->length; $i++) {
                    $t = $messages->item($i)->getAttribute('type');
                    if ( ! empty($t)) {
                        $manifest['handles'][] = $t;
                    }
                }
            }

            $rule = $platform->getElementsByTagName('rule')->item(0);
            if ( ! empty($rule) && $rule->getAttribute('embed') == 'true') {
                $manifest['isrulefields'] = true;
            }

            $card = $platform->getElementsByTagName('card')->item(0);
            if ( ! empty($card) && $card->getAttribute('embed') == 'true') {
                $manifest['iscard'] = true;
            }

            $oauth_type = $platform->getElementsByTagName('oauth')->item(0);
            if ( ! empty($oauth_type) && $oauth_type->getAttribute('type') == OAUTH_TYPE_USERINFO) {
                $manifest['oauth_type'] = OAUTH_TYPE_USERINFO;
            }

            $supports = $platform->getElementsByTagName('supports')->item(0);
            if ( ! empty($supports)) {
                $support_type = $supports->getElementsByTagName('item');
                for ($i = 0; $i < $support_type->length; $i++) {
                    $t = $support_type->item($i)->getAttribute('type');
                    if ( ! empty($t)) {
                        $manifest['supports'][] = $t;
                    }
                }
            }

            $plugins = $platform->getElementsByTagName('plugins')->item(0);
            if ( ! empty($plugins)) {
                $plugin_list = $plugins->getElementsByTagName('item');
                for ($i = 0; $i < $plugin_list->length; $i++) {
                    $plugin = $plugin_list->item($i)->getAttribute('name');
                    if ( ! empty($plugin)) {
                        $manifest['plugins'][] = $plugin;
                    }
                }
            }
        }

        $bindings = $root->getElementsByTagName('bindings')->item(0);
        if ( ! empty($bindings)) {
            foreach (array_keys(self::MODULE_BINDINGS) as $name) {
                $binding = $bindings->getElementsByTagName($name)->item(0);
                if ( ! empty($binding)) {
                    $manifest['bindings'][$name] = $this->parseBinding($binding);
                }
            }
        }

        $permissions = $root->getElementsByTagName('permissions')->item(0);
        if ( ! empty($permissions)) {
            $manifest['permissions'] = [];
            $items = $permissions->getElementsByTagName('entry');
            for ($i = 0; $i < $items->length; $i++) {
                $item = $items->item($i);
                $row = [
                    'title'      => $item->getAttribute('title'),
                    'permission' => $item->getAttribute('do'),
                ];
                if ( ! empty($row['title']) && ! empty($row['permission'])) {
                    $manifest['permissions'][] = $row;
                }
            }
        }

        return $manifest;
    }

    /**
     * @param DOMElement $dom
     * @param string $name
     * @param int $index
     *
     * @return string|null
     */
    protected function getDomTextContent(DOMElement $dom, string $name, int $index = 0)
    {
        return ArrayHelper::getValue($dom->getElementsByTagName($name)->item($index), 'textContent');
    }

    /**
     * @param DOMElement $dom
     *
     * @return array
     */
    protected function parseBinding(DOMElement $dom)
    {
        $binding = [];

        $call = $dom->getAttribute('call');
        if ( ! empty($call)) {
            $binding[] = ['call' => $call];
        }
        $entries = $dom->getElementsByTagName('entry');
        for ($i = 0; $i < $entries->length; $i++) {
            $entry = $entries->item($i);
            $direct = $entry->getAttribute('direct');
            $row = [
                'title'  => $entry->getAttribute('title'),
                'do'     => $entry->getAttribute('do'),
                'direct' => ! empty($direct) && $direct != 'false',
                'state'  => $entry->getAttribute('state'),
                'icon'   => $entry->getAttribute('icon'),
            ];
            if ( ! empty($row['title']) && ! empty($row['do'])) {
                $binding[] = $row;
            }
        }

        return $binding;
    }
}