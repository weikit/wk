<?php

namespace weikit\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weikit\models\Module;

/**
 * ModuleSearch represents the model behind the search form of `weikit\models\Module`.
 */
class ModuleSearch extends Module
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mid', 'settings', 'isrulefields', 'issystem', 'target', 'iscard', 'wxapp_support', 'welcome_support', 'oauth_type', 'webapp_support', 'phoneapp_support', 'account_support', 'xzapp_support'], 'integer'],
            [['name', 'type', 'title', 'version', 'ability', 'description', 'author', 'url', 'subscribes', 'handles', 'permissions', 'title_initial'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Module::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'mid' => $this->mid,
            'settings' => $this->settings,
            'isrulefields' => $this->isrulefields,
            'issystem' => $this->issystem,
            'target' => $this->target,
            'iscard' => $this->iscard,
            'wxapp_support' => $this->wxapp_support,
            'welcome_support' => $this->welcome_support,
            'oauth_type' => $this->oauth_type,
            'webapp_support' => $this->webapp_support,
            'phoneapp_support' => $this->phoneapp_support,
            'account_support' => $this->account_support,
            'xzapp_support' => $this->xzapp_support,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'version', $this->version])
            ->andFilterWhere(['like', 'ability', $this->ability])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'author', $this->author])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'subscribes', $this->subscribes])
            ->andFilterWhere(['like', 'handles', $this->handles])
            ->andFilterWhere(['like', 'permissions', $this->permissions])
            ->andFilterWhere(['like', 'title_initial', $this->title_initial]);

        return $dataProvider;
    }
}
