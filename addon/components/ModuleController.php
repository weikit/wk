<?php

namespace weikit\addon\components;

use weikit\addon\Controller;

abstract class ModuleController extends Controller
{
    /**
     * @param int $rid
     *
     * @return string
     */
    public function fieldsFormDisplay($rid = 0)
    {
        return '';
    }

    /**
     * @param int $rid
     *
     * @return string
     */
    public function fieldsFormValidate($rid = 0)
    {
        return '';
    }

    /**
     * @param int $rid
     */
    public function fieldsFormSubmit($rid)
    {
    }

    /**
     * @param $rid
     *
     * @return bool
     */
    public function ruleDeleted($rid)
    {
        return true;
    }

    /**
     * @param array $settings
     */
    public function settingsDisplay($settings)
    {
        return null;
    }

    public function actionSettings()
    {
        $this->settingsDisplay([]);
    }
}