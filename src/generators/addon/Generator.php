<?php

namespace weikit\generators\addon;

use Yii;
use weikit\services\ModuleService;

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

    public $setting = false;

    public $install;
    public $uninstall;
    public $upgrade;

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
            [['url'], 'string', 'max' => 255],
//            [['supports']]  // todo

            [['setting'], 'boolean'],


            [['install', 'uninstall', 'upgrade'], 'safe']
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

            'supports' => '支持类型',

            'setting' => '设置项',

            'install' => '安装脚本',
            'uninstall' => '卸载脚本',
            'upgrade' => '升级脚本'
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

            'supports' => '模块支持的类型',

            'setting' => '是否有设置页面, 勾选将创建设置页模板',

            'install' => '可以定义为SQL语句. 也可以指定为单个的php脚本文件, 如: install.php',
            'uninstall' => '可以定义为SQL语句. 也可以指定为单个的php脚本文件, 如: uninstall.php',
            'upgrade' => '可以定义为SQL语句. 也可以指定为单个的php脚本文件, 如: upgrade.php'
        ];
    }

    public $generateMap =  [
        [
            'target' => 'module.php',
            'template' => 'module.php',
        ],
        [
            'target' => 'receiver.php',
            'template' => 'receiver.php',
        ],
        [
            'target' => 'processor.php',
            'template' => 'processor.php',
        ],
        [
            'target' => 'manifest.xml',
            'template' => 'manifest.xml.php',
        ],
        [
            'target' => 'template/setting.html',
            'template' => 'template/setting.html.php',
            'when' => 'setting'
        ],
    ];

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];

        // module.php
        $files[] = Yii::createObject(CodeFile::class, [
            $this->service->getRealPath($this->identifie, 'module.php'),
            $this->render('module.php')
        ]);

        // receiver.php
        $files[] = Yii::createObject(CodeFile::class, [
            $this->service->getRealPath($this->identifie, 'receiver.php'),
            $this->render('receiver.php')
        ]);

        // processor.php
        $files[] = Yii::createObject(CodeFile::class, [
            $this->service->getRealPath($this->identifie, 'processor.php'),
            $this->render('processor.php')
        ]);

        // manifest.xml
        $files[] = Yii::createObject(CodeFile::class, [
            $this->service->getRealPath($this->identifie, 'manifest.xml'),
            $this->render('manifest.xml.php')
        ]);

        // template/setting.html
        if ($this->setting) {
            $files[] = Yii::createObject(CodeFile::class, [
                $this->service->getRealPath($this->identifie, 'template/setting.html'),
                $this->render('template/setting.html.php')
            ]);
        }

        // install.php
        if ($this->install && pathinfo($this->install, PATHINFO_EXTENSION) === 'php') {
            $files[] = Yii::createObject(CodeFile::class, [
                $this->service->getRealPath($this->identifie, 'install.php'),
                $this->render('install.php')
            ]);
        }

        // uninstall.php
        if ($this->uninstall && pathinfo($this->uninstall, PATHINFO_EXTENSION) === 'php') {
            $files[] = Yii::createObject(CodeFile::class, [
                $this->service->getRealPath($this->identifie, 'uninstall.php'),
                $this->render('install.php')
            ]);
        }

        // upgrade.php
        if ($this->upgrade && pathinfo($this->upgrade, PATHINFO_EXTENSION) === 'php') {
            $files[] = Yii::createObject(CodeFile::class, [
                $this->service->getRealPath($this->identifie, 'upgrade.php'),
                $this->render('install.php')
            ]);
        }

        return $files;
    }
}
