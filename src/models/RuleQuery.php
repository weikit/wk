<?php

namespace weikit\models;

use yii\db\ActiveQuery;

class RuleQuery extends ActiveQuery
{

    /**
     * 查询状态
     *
     * @param int $status 启用
     *
     * @return $this
     */
    public function active($status = Rule::STATUS_ACTIVE)
    {
        [, $alias] = $this->getTableNameAndAlias();

        return $this->andWhere([$alias . '.status' => $status]);
    }
}