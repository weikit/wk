<?php
/* @var $this yii\web\View */
/* @var $generator \weikit\generators\addon\Generator */
?>
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns="http://www.0516city.com" versionCode="0.6">
    <application setting="false">
        <name><![CDATA[<?= $generator->name ?>]]></name>
        <identifie><![CDATA[<?= $generator->identifie ?>]]></identifie>
        <version><![CDATA[<?= $generator->version ?>]]></version>
        <type><![CDATA[business]]></type>
        <ability><![CDATA[<?= $generator->ability ?>p]]></ability>
        <description><![CDATA[<?= $generator->description ?>]]></description>
        <author><![CDATA[<?= $generator->author ?>]]></author>
        <url><![CDATA[<?= $generator->url ?>]]></url>
    </application>
    <platform>
        <subscribes>
        </subscribes>
        <handles>
            <message type="text" />
        </handles>
        <rule embed="false" />
        <card embed="false" />
        <supports>
            <item type="wxapp" />
            <item type="app" />
        </supports>
    </platform>
    <bindings>
        <cover>
            <entry title="手机端社区入口" do="index" state="" direct="false" />
        </cover>
        <menu>
            <entry title="独立后台入口" do="index" state="" direct="false" />
        </menu>
    </bindings>
    <install><![CDATA[]]></install>
    <uninstall><![CDATA[]]></uninstall>
    <upgrade><![CDATA[upgrade.php]]></upgrade>
</manifest>