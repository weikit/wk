<?php

namespace weikit\services;

use weikit\models\ModuleBinding;
use Yii;
use weikit\models\Module;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use weikit\core\service\BaseService;
use weikit\models\search\ModuleSearch;
use weikit\models\form\InactiveModuleForm;
use weikit\exceptions\AddonModuleNotFoundException;

class ModuleService extends BaseService
{
    /**
     * @param $name
     *
     * @return Module|null
     * @throw \weikit\core\exceptions\ModelNotFoundException
     */
    public function findByName($name)
    {
        return Module::tryFindOne(['name' => $name]);
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

            foreach([$addon['install'], $addon['upgrade']] as $script) {
            }
        });

        return $module;
    }
}