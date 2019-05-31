<?php

namespace weikit\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weikit\models\Rule;

/**
 * RuleSearch represents the model behind the search form of `weikit\models\Rule`.
 */
class RuleSearch extends Rule
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'uniacid', 'status', 'displayorder'], 'integer'],
            [['name', 'module'], 'safe'],
        ];
    }

    /**
     * @param \yii\web\Request|array $requestOrData
     *
     * @return ActiveDataProvider
     */
    public function search($requestOrData = [])
    {
        $query = Rule::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->loadFrom($requestOrData);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'uniacid' => $this->uniacid,
            'status' => $this->status,
            'displayorder' => $this->displayorder,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'module', $this->module]);

        return $dataProvider;
    }
}
