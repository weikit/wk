<?php

namespace weikit\core\db;

use weikit\core\exceptions\ModelValidationException;
use Yii;
use weikit\core\exceptions\ModelNotFoundException;

class ActiveRecord extends \yii\db\ActiveRecord
{
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
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function trySave($attributeNames = null)
    {
        $isNewRecord = $this->getIsNewRecord();

        if(!$this->validate($attributeNames)) {
            $title = $isNewRecord ? 'inserted' : 'updated';
            Yii::info('Model not ' . $title . ' to validation error.', __METHOD__);
            throw new ModelValidationException($this);
        }

        $runValidation = false;

        if ($isNewRecord) {
            return $this->insert($runValidation, $attributeNames);
        }

        return $this->update($runValidation, $attributeNames) !== false;
    }
}