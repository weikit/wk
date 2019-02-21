<?php

namespace weikit\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weikit\models\WechatAccount;

/**
 * AccountWechatSearch represents the model behind the search form of `weikit\models\AccountWechat`.
 */
class WechatAccountSearch extends WechatAccount
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['acid', 'uniacid', 'level', 'lastupdate', 'styleid'], 'integer'],
            [['token', 'encodingaeskey', 'name', 'account', 'original', 'signature', 'country', 'province', 'city', 'username', 'password', 'key', 'secret', 'subscribeurl', 'auth_refresh_token'], 'safe'],
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
        $query = WechatAccount::find();

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
            'level' => $this->level,
            'lastupdate' => $this->lastupdate,
            'styleid' => $this->styleid,
        ]);

        $query->andFilterWhere(['like', 'token', $this->token])
            ->andFilterWhere(['like', 'encodingaeskey', $this->encodingaeskey])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'account', $this->account])
            ->andFilterWhere(['like', 'original', $this->original])
            ->andFilterWhere(['like', 'signature', $this->signature])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'province', $this->province])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'key', $this->key])
            ->andFilterWhere(['like', 'secret', $this->secret])
            ->andFilterWhere(['like', 'subscribeurl', $this->subscribeurl])
            ->andFilterWhere(['like', 'auth_refresh_token', $this->auth_refresh_token]);

        return $dataProvider;
    }
}
