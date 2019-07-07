<?php

namespace weikit\services;

use Yii;
use yii\web\Request;
use yii\helpers\ArrayHelper;
use yii\base\InvalidValueException;
use weikit\models\Rule;
use weikit\core\Service;
use weikit\models\RuleKeyword;
use weikit\models\search\RuleSearch;

class ReplyService extends Service
{
    /**
     * @var string|Rule
     */
    public $modelClass = Rule::class;

    /**
     * @param $acid
     * @param array $options
     *
     * @return Rule|null
     * @throws \weikit\core\exceptions\ModelNotFoundException
     */
    public function findRuleById($id, array $options = [])
    {
        return $this->findOne(['id' => $id], $options);
    }

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

    /**
     * @param Rule $model
     * @param Request|array $requestOrData
     * @param RuleKeyword|null $keywordModel
     *
     * @return bool
     * @throws \Throwable
     */
    public function saveRule(Rule $model, $requestOrData, $keywordModel = null)
    {
        if ($model->loadFrom($requestOrData)) {
            $model::getDb()->transaction(function() use ($requestOrData, $model) {
                /** @var AccountService $accountServcer */
                $accountService = Yii::createObject(AccountService::class);
                /** @var RuleKeyword $keywordModel */
                $keywordModel = $keywordModel !== null ? $keywordModel : Yii::createObject(RuleKeyword::class);
                $model->uniacid = $accountService->managingUniacid;
                $model->trySave();
                if ($requestOrData instanceof Request) {
                    $newKeywords = $requestOrData->post($keywordModel->formName());
                } else {
                    $newKeywords = $requestOrData['keywords'] ?? [];
                }
                if (!is_array($newKeywords) || empty($newKeywords)) {
                    throw new InvalidValueException('The keywords of rule must be set and be array.');
                }
                $oldKeywords = $model->keywords;

                $newKeywordIds = array_filter(ArrayHelper::getColumn($newKeywords, 'id'));
                $oldKeywordIds = array_filter(ArrayHelper::getColumn($oldKeywords, 'id'));

                $deleteKeywordIds = array_diff($oldKeywordIds, $newKeywordIds);
                foreach ($newKeywords as $keyword) {
                    $id = $keyword['id'] ?? 0;
                    if (!$id) { // 添加新关键字
                        $_keywordModel = clone $keywordModel;
                        $_keywordModel->load($keyword, '');
                        $_keywordModel->setAttributes([
                            'uniacid' => $model->uniacid,
                            'rid' => $model->id,
                            'module' => $model->module,
                        ]);
                        $_keywordModel->trySave();
                    }
                }
                if (!empty($deleteKeywordIds)) { // 移除删除的关键字
                    RuleKeyword::deleteAll(['id' => $deleteKeywordIds]);
                }
            });

            return true;
        }
        return false;
    }
}