<?php

namespace weikit\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weikit\models\WechatAccount;

/**
 * WechatAccountSearch represents the model behind the search form of `weikit\models\WechatAccount`.
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
}
