<?php

namespace weikit\services;

use Yii;
use DOMElement;
use DOMDocument;
use RuntimeException;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use weikit\models\Module;
use weikit\models\ModuleBinding;
use weikit\core\addon\ModuleSite;
use weikit\core\service\BaseService;
use weikit\models\search\ModuleSearch;
use weikit\exceptions\AddonModuleNotFoundException;

class ModuleService extends BaseService
{
    /**
     * @var string
     */
    public $modelClass = Module::class;
    /**
     * 插件基本路由
     *
     * @var string
     */
    public $baseUrl = '@wp_url/addons';
    /**
     * 插件基本路径
     *
     * @var string
     */
    public $basePath = '@wp/addons';
    /**
     * 插件设置文件名
     *
     * @var string
     */
    public $configFile = 'manifest.xml';
    /**
     * @var array
     */
    public $bindings = [
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
     * 获取插件Url
     *
     * @param $name
     *
     * @return bool|string
     */
    public function getUrl($name)
    {
        return Yii::getAlias($this->baseUrl . '/' . $name);
    }

    /**
     * 获取扩展模块虚拟路径
     *
     * @param $name
     * @param null $file
     *
     * @return string
     */
    public function getVirtualPath($name, $file = null)
    {
        return $this->basePath . '/' . $name . '/' . $file;
    }

    /**
     * 获取扩展模块真实路径
     *
     * @param $name
     * @param null $file
     *
     * @return bool|string
     */
    public function getRealPath($name, $file = null)
    {
        return Yii::getAlias($this->getVirtualPath($name, $file));
    }

    /**
     * @param $mid
     * @param array $options
     *
     * @return Module|null
     * @throw \weikit\core\exceptions\ModelNotFoundException
     */
    public function findByMid($mid, array $options = [])
    {
        return $this->findBy(['mid' => $mid], $options);
    }

    /**
     * @param $name
     * @param array $options
     *
     * @return Module|null
     * @throw \weikit\core\exceptions\ModelNotFoundException
     */
    public function findByName($name, array $options = [])
    {
        return $this->findBy(['name' => $name], $options);
    }

    /**
     * @param string|int $eid
     * @param array $options
     *
     * @return ModuleBinding|null
     * @throw \weikit\core\exceptions\ModelNotFoundException
     */
    public function findEntryByEid($eid, array $options = [])
    {
        return $this->findEntryBy(['eid' => $eid], $options);
    }

    /**
     * @param $condition
     * @param array $options
     *
     * @return ModuleBinding|null
     * @throw \weikit\core\exceptions\ModelNotFoundException
     */
    public function findEntryBy($condition, array $options = [])
    {
        return $this->findBy($condition, array_merge($options, [
            'modelClass' => ModuleBinding::class
        ]));
    }


    /**
     * @param array $condition
     * @param array $options
     *
     * @return ModuleBinding[]
     */
    public function findAllEntryBy($condition, array $options = [])
    {
        return $this->findAllBy($condition, array_merge($options, [
            'modelClass' => ModuleBinding::class
        ]));
    }

    /**
     * @param $name
     *
     * @return array|null
     * @throws AddonNotFoundException
     */
    public function findInactiveByName(string $name)
    {
        $module = $this->findAvailableByName($name);
        if ($module === null) {
            throw new AddonModuleNotFoundException($name);
        }

        return $module;
    }

    /**
     * @param yii\web\Request|array $requestOrData
     *
     * @return array
     */
    public function search($requestOrData)
    {
        $model = Yii::createObject(ModuleSearch::class);

        $query = Module::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->isModelLoad($model, $requestOrData);
        if ($model->validate()) {
            $query->andFilterWhere([
                'mid'              => $model->mid,
                'settings'         => $model->settings,
                'isrulefields'     => $model->isrulefields,
                'issystem'         => $model->issystem,
                'target'           => $model->target,
                'iscard'           => $model->iscard,
                'wxapp_support'    => $model->wxapp_support,
                'welcome_support'  => $model->welcome_support,
                'oauth_type'       => $model->oauth_type,
                'webapp_support'   => $model->webapp_support,
                'phoneapp_support' => $model->phoneapp_support,
                'account_support'  => $model->account_support,
                'xzapp_support'    => $model->xzapp_support,
            ])
                  ->andFilterWhere(['like', 'name', $model->name])
                  ->andFilterWhere(['like', 'type', $model->type])
                  ->andFilterWhere(['like', 'title', $model->title])
                  ->andFilterWhere(['like', 'version', $model->version])
                  ->andFilterWhere(['like', 'ability', $model->ability])
                  ->andFilterWhere(['like', 'description', $model->description])
                  ->andFilterWhere(['like', 'author', $model->author])
                  ->andFilterWhere(['like', 'url', $model->url])
                  ->andFilterWhere(['like', 'subscribes', $model->subscribes])
                  ->andFilterWhere(['like', 'handles', $model->handles])
                  ->andFilterWhere(['like', 'permissions', $model->permissions])
                  ->andFilterWhere(['like', 'title_initial', $model->title_initial]);
        }

        return [
            'searchModel'  => $model,
            'dataProvider' => $dataProvider,
        ];
    }

    /**
     * 获取可安装扩展模块列表
     *
     * @return array
     */
    public function searchInactive()
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $this->findAvailable(),
        ]);

        return ['dataProvider' => $dataProvider];
    }

    /**
     * 启用扩展模块
     *
     * @param string $name
     */
    public function activate(string $name)
    {
        $addon = $this->findInactiveByName($name);

        /* @var $module Module */
        $module = Yii::createObject(Module::class);
        $module->getDb()->transaction(function () use ($module, $addon) {

            // 1. 添加扩展模块
            $module->setAttributes($addon);
            $module->trySave();

            // 2. 注册扩展模块功能入口
            foreach ($addon['bindings'] as $entry => $bindings) {
                foreach($bindings as $binding) {
                    $bindingModel = Yii::createObject(ModuleBinding::class);
                    $bindingModel->setAttributes(array_merge([
                        'entry' => $entry,
                        'module' => $module->name,
                    ], $binding));
                    $bindingModel->trySave();
                }
            }

            // 3. 扩展模块生成数据
            $scripts = [$addon['install']];
            // alert table 出错的话事务会影式提交, 确保结构操作操作不会出问题
            // @see https://github.com/yiisoft/yii2/issues/17173#issuecomment-467946276
            array_walk($scripts, function($script, $key, $db) use ($addon) {
                /* @var $db \yii\db\Connection */
                if (!empty($script)) {
                    if (strtolower(substr($script, -4)) === '.php') {
                        $path = $this->getRealPath($addon['name'], $script);

                        if (file_exists($path)) {
                            require $path;
                        }
                    } else {
                        // @see https://stackoverflow.com/questions/7690380/regular-expression-to-match-all-comments-in-a-t-sql-script/13821950#13821950 移除注释
                        $script = preg_replace( '@(([\'"]).*?[^\\\]\2)|((?:\#|--).*?$|/\*(?:[^/*]|/(?!\*)|\*(?!/)|(?R))*\*\/)\s*|(?<=;)\s+@ms', '$1', $script );
                        // 替换前缀
                        $script = str_replace(' ims_', ' ' . $db->tablePrefix, $script);
                        $script = str_replace(' `ims_', ' `' . $db->tablePrefix, $script);

                        foreach(explode(';', $script) as $sql) {
                            if (!empty($sql)) {
                                $db->createCommand($sql)->query();
                            }
                        }
                    }
                }
            }, $module->getDb());
        });

        return $module;
    }

    /**
     * 获取可用扩展模块列表
     *
     * @return array
     */
    public function findAvailable()
    {
        return $this->scanAvailable();
    }

    /**
     * 获取指定可用扩展模块
     *
     * @param string $name
     *
     * @return array|null
     */
    public function findAvailableByName(string $name)
    {
        $addon = $this->scanAvailable($name);

        return empty($addon) ? null : $addon;
    }

    /**
     * @param null|string $name
     *
     * @return array
     */
    protected function scanAvailable($name = null)
    {
        $list = [];
        $path = Yii::getAlias($this->path);

        if (is_dir($path)) {
            /* @var $configFile SplFileInfo */
            foreach (Finder::create()->in($path)->files()->depth(1)->name($this->configFile) as $configFile) {
                $manifest = $this->parse($configFile->getContents());
                if (empty($manifest)) {
                    continue;
                }
                if ($name !== null && $manifest['name'] === $name) {
                    return $manifest;
                }
                $list[] = $manifest;
            }
        }

        return $list;
    }

    /**
     * 解析设置
     *
     * @param string $content
     *
     * @return array
     */
    public function parse(string $content)
    {   // TODO 待调整优化
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
            if ( ! empty($this->bindings)) {
                foreach (array_keys($this->bindings) as $name) {
                    $binding = $bindings->getElementsByTagName($name)->item(0);
                    if ( ! empty($binding)) {
                        $manifest['bindings'][$name] = $this->parseBinding($binding);
                    }
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

    /**
     * @param Module $module
     *
     * @return mixed
     */
    public function instanceSite(Module $module)
    {
        $class = $module->name . 'ModuleSite';
        if (!class_exists($class)) {
            require_once $this->getRealPath($module->name, 'site.php');
        }
        if (!class_exists($class)) {
            list($namespace) = explode('_', $module->name);
            if (class_exists('\\' . $namespace . '\\' . $class)) {
                $class = '\\' . $namespace . '\\' . $class;
            }
        }

        $site = Yii::createObject([
            'class' => $class,
            'module' => $module,
        ]);

        if (!$site instanceof ModuleSite) {
            throw new RuntimeException('The module class "'. $class  .'" must extend "' . ModuleSite::class . '"');
        }

        return $site;
    }
}