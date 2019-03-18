<?php

namespace weikit\core\addon;

use weikit\models\Module;
use yii\base\BaseObject;

abstract class Base extends BaseObject
{
    /**
     * @var int
     */
    public $uniacid;
    /**
     * @var Module
     */
    public $module;


    public function init()
    {
        if ($this->uniacid === null) {
            global $_W;
            $this->uniacid = $_W['uniacid'];
        }
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->module->name;
    }
}