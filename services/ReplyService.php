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
     * @param array $options
     *
     * @return RuleSearch
     * @throws \yii\base\InvalidConfigException
     */
    public function createSearch(array $options = [])
    {
        return Yii::createObject(array_merge($options, [
            'class' => RuleSearch::class
        ]));
    }
}