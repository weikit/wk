<?php

namespace weikit\models;

use yii\db\ActiveQuery;

class RuleKeywordQuery extends ActiveQuery
{
    /**
     * 文本类型关键字过滤
     *
     * @param string $keyword
     *
     * @return $this
     */
    public function keyword($keyword)
    {
        return $this->andWhere([
            'or',
            ['and', '{{type}}=:typeMatch', '{{content}}=:keyword'], // 直接匹配关键字
            ['and', '{{type}}=:typeInclude', 'INSTR(:keyword, {{content}})>0'], // 包含关键字
            ['and', '{{type}}=:typeRegular', ':keyword REGEXP {{content}}'], // 正则匹配关键字
        ])
        ->addParams([
            ':keyword'     => $keyword,
            ':typeMatch'   => RuleKeyword::TYPE_MATCH,
            ':typeInclude' => RuleKeyword::TYPE_INCLUDE,
            ':typeRegular' => RuleKeyword::TYPE_REGULAR,
        ]);
    }

    /**
     * @return $this
     */
    public function withRuleByUniacid($uniacid, $status = Rule::STATUS_ACTIVE)
    {
        return $this->joinWith([
            'rule' => function($query) use ($uniacid, $status) {
                /* @var RuleQuery $query */
                $query->alias('rule');
                if ($status !== null) {
                    $query->active($status);
                }
                $query->andWhere(['{{uniacid}}' => $uniacid]);
            }
        ]);
    }
}