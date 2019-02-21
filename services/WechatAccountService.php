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
     * @param $acid
     *
     * @return WechatAccount|null
     * @throws weikit\core\exceptions\ModelValidationException
     */
    public function findByAcid($id)
    {
        return WechatAccount::tryFindOne($id);
    }

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
        $model = new WechatAccountForm();

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
     * @param $acid
     * @param $requestOrData
     *
     * @return WechatAccount
     * @throws \weikit\core\exceptions\ModelNotFoundException
     * @throws \weikit\core\exceptions\ModelValidationException
     */
    public function editByAcid($acid, $requestOrData)
    {
        $model = $this->findByAcid($acid);
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
     * @param $acid
     *
     * @return false|int
     * @throws \weikit\core\exceptions\ModelNotFoundException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteByAcid($acid)
    {
        $model = $this->findByAcid($acid);

        return $model->delete();
    }
}