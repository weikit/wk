<?php

namespace weikit\services;

use weikit\core\Service;
use weikit\models\Module;
use weikit\models\ModuleSearch;

class ModuleService extends Service
{
    /**
     * @param array $query
     *
     * @return array
     */
    public function search(array $query)
    {
        $searchModel = new ModuleSearch();
        $dataProvider = $searchModel->search($query);
        return compact('searchModel', 'dataProvider');
    }

    /**
     * @param $id
     *
     * @return Module|null
     */
    public function findById($id)
    {
        return Module::findOne($id);
    }

    /**
     * @param Request|array $request
     *
     * @return Module
     */
    public function add($requestOrData)
    {
        $model = new Module();

        if (
            $requestOrData instanceof Request ?
            $model->load($requestOrData->post()) :
            $model->load($requestOrData, '')
        ) {
            $model->save();
        }

        return $model;
    }

    /**
     * @param $id
     * @param Request|array $request
     *
     * @return Module|null
     */
    public function editById($id, $requestOrData)
    {
        $model = $this->findById($id);
        if (
            $model !== null &&
            (
                $requestOrData instanceof Request ?
                $model->load($requestOrData->post()) :
                $model->load($requestOrData, '')
            )
        ) {
            $model->save();
        }
        return $model;
    }

    /**
     * @param $id
     *
     * @return false|int|null
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteById($id)
    {
        $model = $this->findById($id);

        if ($model !== null) {
            return $model->delete();
        }

        return $model;
    }
}