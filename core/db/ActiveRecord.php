<?php

namespace weikit\core\db;

use weikit\core\model\ModelTryTrait;
use weikit\core\model\ModelLoadTrait;
use weikit\core\exceptions\ModelNotFoundException;


class ActiveRecord extends \yii\db\ActiveRecord
{
    use ModelTryTrait;
    use ModelLoadTrait;

    /**
     * @see ActiveRecord::save
     * @throws ModelNotFoundException if model not find
     */
    public static function tryFindOne($condition)
    {
        $model = static::findOne($condition);

        if ($model === null) {
            throw new ModelNotFoundException(static::class);
        }

        return $model;
    }

    /**
     * @see ActiveRecord::save
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws weikit\core\exceptions\ModelValidationException
     */
    public function trySave($attributeNames = null)
    {
        $this->tryValidate($attributeNames);

        $runValidation = false;

        if ($this->getIsNewRecord()) {
            return $this->insert($runValidation, $attributeNames);
        }

        return $this->update($runValidation, $attributeNames) !== false;
    }
}