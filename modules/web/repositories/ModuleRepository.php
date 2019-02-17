<?php

namespace weikit\modules\web\repositories;

use weikit\core\service\Repository;
use weikit\modules\web\models\ModuleSearch;

class ModuleRepository extends Repository
{
    public function list($params)
    {
        $model = new ModuleSearch();
        return [
            'searchModel' => $model,
            'dataProvider' => $model->search($params)
        ];
    }
}