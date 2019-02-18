<?php

namespace weikit\modules\web\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weikit\models\Account;

/**
 * AccountSearch represents the model behind the search form of `weikit\models\Account`.
 */
class AccountSearch extends Account
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['acid', 'uniacid', 'type', 'isconnect', 'isdeleted', 'endtime'], 'integer'],
            [['hash'], 'safe'],
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
        $query = Account::find();

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
            'acid' => $this->acid,
            'uniacid' => $this->uniacid,
            'type' => $this->type,
            'isconnect' => $this->isconnect,
            'isdeleted' => $this->isdeleted,
            'endtime' => $this->endtime,
        ]);

        $query->andFilterWhere(['like', 'hash', $this->hash]);

        return $dataProvider;
    }
}
