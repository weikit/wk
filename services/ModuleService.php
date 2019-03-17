<?php

namespace weikit\services;


use Yii;
use weikit\models\Module;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use weikit\core\service\BaseService;
use weikit\models\search\ModuleSearch;
use weikit\models\form\InactiveModuleForm;
use weikit\exceptions\AddonModuleNotFoundException;
use weikit\models\ModuleBinding;

class ModuleService extends BaseService
{
    /**
     * @var string
     */
    public $modelClass = Module::class;
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
        $addon = Yii::$app->addon->findAvailableByName($name);
        if ($addon === null) {
            throw new AddonModuleNotFoundException($name);
        }

        return $addon;
    }

    /**
     * @param Request|array $requestOrData
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
            'allModels' => Yii::$app->addon->findAvailable(),
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
                        $path = Yii::$app->addon->getPath($addon['name'], $script);

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
     * @param Module $module
     *
     * @return mixed
     */
    public function instanceSite(Module $module)
    {
        $class = $module->name . 'ModuleSite';
        if (!class_exists($class)) {
            require_once Yii::$app->addon->getPath($module->name, 'site.php');
        }
        if (!class_exists($class)) {
            list($namespace) = explode('_', $module->name);
            if (class_exists('\\' . $namespace . '\\' . $class)) {
                $class = '\\' . $namespace . '\\' . $class;
            }
        }
        $site = Yii::createObject($class);
        return $site;
    }
}