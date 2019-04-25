<?php

namespace weikit\services;

use Yii;
use weikit\models\Rule;
use weikit\core\service\BaseService;
use weikit\models\search\RuleSearch;

class ReplyService extends BaseService
{
    /**
     * @var string|Rule
     */
    public $modelClass = Rule::class;

    /**
     * @param yii\web\Request|array $requestOrData
     *
     * @return array
     */
    public function search($requestOrData = [])
    {
        $model = Yii::createObject(RuleSearch::class);

        $query = $this->modelClass::find();
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
}