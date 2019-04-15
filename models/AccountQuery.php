<?php

namespace weikit\models;

use yii\db\ActiveQuery;

class AccountQuery extends ActiveQuery
{
    /**
     * 激活状态
     * @return static
     */
    public function connected($connected = 1)
    {
        return $this->andWhere(['isconnected' => $connected]);
    }
}

