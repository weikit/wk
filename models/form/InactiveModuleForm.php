<?php

namespace weikit\models\form;

use weikit\core\model\Model;

class InactiveModuleForm extends Model
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $version;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $ability;
    /**
     * @var string
     */
    public $author;
    /**
     * @var string
     */
    public $url;
    /**
     * @var string
     */
    public $description;
    /**
     * @var bool
     */
    public $setting;
    /**
     * @var array
     */
    public $subscribes = [];
    /**
     * @var array
     */
    public $handles = [];
    /**
     * @var bool
     */
    public $isrulefields;
    /**
     * @var bool
     */
    public $iscard;
    /**
     * @var array
     */
    public $supports;
    /**
     * @var int
     */
    public $oauth_type;

    /**
     * @var string
     */
    public $install;
    /**
     * @var string
     */
    public $uninstall;
    /**
     * @var string
     */
    public $upgrade;

    /**
     * @var array
     */
    public $plugins = [];
    /**
     * @var array
     */
    public $bindings;
    /**
     * @var array
     */
    public $permissions;
}