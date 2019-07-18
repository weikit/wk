<?php

namespace weikit\services;

use Yii;
use yii\caching\TagDependency;
use yii\data\ArrayDataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use weikit\core\Service;
use weikit\models\Module;
use weikit\models\ModuleBinding;
use weikit\core\addon\SiteAction;
use weikit\core\addon\ModuleBuilder;
use weikit\core\addon\ModuleParser;
use weikit\models\UniAccountModule;
use weikit\models\search\ModuleSearch;
use weikit\exceptions\AddonModuleNotFoundException;

/**
 * Class ModuleService
 * @package weikit\services
 */
class ModuleService extends Service
{
    const CACHE_TAG_ADDON_MODULES = 'addon_modules';

    /**
     * @var string
     */
    public $modelClass = Module::class;
    /**
     * @var string
     */
    public $entryModelClass = ModuleBinding::class;
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
     * 核心插件基本路径
     *
     * @var string
     */
    public $coreBasePath = '@weikit/addons';
    /**
     * 插件设置文件名
     *
     * @var string
     */
    public $configFile = 'manifest.xml';


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
        return $this->findOne(['mid' => $mid], $options);
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
        return $this->findOne(['name' => $name], $options);
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
        return $this->findOne($condition, array_merge($options, [
            'modelClass' => $this->entryModelClass
        ]));
    }


    /**
     * @param array $condition
     * @param array $options
     *
     * @return ModuleBinding[]
     */
    public function findEntriesBy($condition, array $options = [])
    {
        return $this->findAll($condition, array_merge($options, [
            'modelClass' => $this->entryModelClass
        ]));
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function findEntriesByModuleName($name)
    { // TODO cache
        $module = $this->service->findByName($name, [
            'query' => function($query) {
                $query->with('entries');
            }
        ]);
        return $module->entries;
    }

    /**
     * 获取指定账户下的模块设置
     *
     * @param string $module
     * @param init $uniacid
     *
     * @return UniAccountModule|null
     */
    public function findAccountSettings($module, $uniacid)
    {   // TODO cache?
        return $this->findOne([
            'uniacid' => $uniacid,
            'module' => $module,
        ], [
            'modelClass' => UniAccountModule::class,
            'exception' => false,
        ]);
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
     * @param array $options
     *
     * @return ModuleSearch
     * @throws \yii\base\InvalidConfigException
     */
    public function createSearch(array $options = [])
    {
        return Yii::createObject(array_merge($options, [
            'class' => ModuleSearch::class
        ]));
    }

    /**
     * 获取未安装扩展模块列表
     * // TODO switch to ModuleSearch
     * @return array
     */
    public function searchAvailable()
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
                    /** @var ModuleBinding $bindingModel */
                    $bindingModel = Yii::createObject([
                        'class' => ModuleBinding::class,
                        'attributes' => array_merge([
                            'entry' => $entry,
                            'module' => $module->name,
                        ], $binding)
                    ]);
                    $bindingModel->trySave();
                }
            }

            // 3. 扩展模块生成数据
            $scripts = [$addon['install']];
            // alert table 出错的话事务会隐式提交, 确保结构操作操作不会出问题
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
        // 标记缓存更新
        TagDependency::invalidate(Yii::$app->cache,  self::CACHE_TAG_ADDON_MODULES);

        Yii::createObject(ModuleBuilder::class)->build();

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
        return $this->scanAvailable($name);
    }

    /**
     * @param null|string $name
     *
     * @return array|null
     */
    protected function scanAvailable($name = null)
    {
        $list = [];
        $path = Yii::getAlias($this->basePath);
        $corePath = Yii::getAlias($this->coreBasePath);

        if (is_dir($path)) {
            /* @var $configFile SplFileInfo */
            foreach (Finder::create()->in([$path, $corePath])->files()->depth(1)->name($this->configFile) as $configFile) {
                /** @var ModuleParser $parser */
                $parser = Yii::createObject([
                    'class' => ModuleParser::class,
                    'content' => $configFile->getContents()
                ]);

                if ($parser->isValid) {
                    $manifest = $parser->data;

                    // 标记系统模块
                    if ($configFile->getFilename() == $corePath) {
                        $manifest['type'] = 'system';
                    }

//                    $list[$manifest['name']] = $manifest;

                    $list[$manifest['name']] = Yii::createObject([
                        'class' => Module::class,
                        'attributes' => $manifest
                    ]);

                }
            }
        }

        if ($name !== null) {
            return array_key_exists($name, $list) ? $list[$name] : null;
        }

        return $list;
    }
}