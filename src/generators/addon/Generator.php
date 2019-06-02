<?php

namespace weikit\generators\addon;

use weikit\services\ModuleService;
use yii\gii\CodeFile;

class Generator extends \yii\gii\Generator
{
    /**
     * @var ModuleService
     */
    protected $service;

    public $name;
    public $identifie;
    public $version = '1.0.0';
    public $ability;
    public $description;
    public $author;
    public $url;
    public $supports = ['wechat'];

    public function __construct(ModuleService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Weikit扩展模块生成器';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return '该生成器将帮助您生成一个基础的Weikit扩展模块.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['identifie', 'name', 'version', 'ability', 'description'], 'required'],

            [['version'], 'match', 'pattern' => '/^\d+[.\d]+\d+$/', 'message' => '{attribute}只能包含数字和.符号并符合版本命名规则, 例如<code>1.0.0</code>'],

            [['author'], 'string', 'max' => 100],
            [['link'], 'string', 'max' => 255],
//            [['supports']]  // todo
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '模块名称',
            'identifie' => '模块标识',
            'version' => '版本',
            'ability' => '模块简述',
            'description' => '模块描述',
            'author' => '作者',
            'url' => '模块链接',

            'supports' => '支持类型'
        ];
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return [
            'name' => '模块名称是模块的一个简称',
            'identifie' => '模块ID必须是唯一的. 比如:<code>example</code>',
            'version' => '模块的版本号',
            'ability' => '简单描述功能',
            'description' => '模块详细描述',
            'author' => '模块的开发作者',
            'url' => '模块更多详情链接',

            'supports' => '模块支持的类型'
        ];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];

        $files[] = new CodeFile(
            $this->service->getRealPath($this->identifie, 'mdoule.php'),
            $this->render('module.php')
        );

        return $files;
    }
}
