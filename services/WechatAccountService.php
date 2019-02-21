<?php

namespace weikit\services;

use Yii;
use yii\web\Request;
use weikit\models\WechatAccount;
use weikit\core\service\BaseService;
use weikit\models\WechatAccountSearch;
use weikit\models\form\WechatAccountForm;

class WechatAccountService extends BaseService
{
    /**
     * @param array $query
     *
     * @return array
     */
    public function search(array $query)
    {
        $searchModel = new WechatAccountSearch();
        $dataProvider = $searchModel->search($query);
        return compact('searchModel', 'dataProvider');
    }

    /**
     * 添加微信公众号账户
     *
     * @param Request|array $requestOrData
     *
     * @return AccountWechatForm|WechatAccount 添加成功返回Account, 失败返回AccountWechatForm
     */
    public function add($requestOrData)
    {
        $model = Yii::createObject(WechatAccountForm::class);

        if (
            $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '')
        ) {
            return $model->tryAdd();
        }

        return $model;
    }

    /**
     * @param $id
     * @param $requestOrData
     *
     * @return WechatAccount
     * @throws \weikit\core\exceptions\ModelNotFoundException
     * @throws \weikit\core\exceptions\ModelValidationException
     */
    public function editById($id, $requestOrData)
    {
        $model = WechatAccount::tryFindOne($id);
        if (
            $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '')
        ) {
            $model->trySave();
        }
        return $model;
    }

    /**
     * @param $id
     *
     * @return false|int
     * @throws \weikit\core\exceptions\ModelNotFoundException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteById($id)
    {
        $model = WechatAccount::tryFindOne($id);

        return $model->delete();
    }
}