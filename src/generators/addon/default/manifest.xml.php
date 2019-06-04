<?php
/* @var $this yii\web\View */
/* @var $generator \weikit\generators\addon\Generator */
?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>

<?= '<manifest xmlns="http://weikit.cn" versionCode="0.6">' ?>

    <application setting="<?= $generator->setting ? 'true' : 'false' ?>">
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
            <message type="text" />
            <message type="image" />
            <message type="voice" />
            <message type="video" />
            <message type="shortvideo" />
            <message type="location" />
            <message type="link" />
            <message type="subscribe" />
            <message type="unsubscribe" />
            <message type="qr" />
            <message type="trace" />
            <message type="click" />
            <message type="view" />
            <message type="merchant_order" />
        </subscribes>
        <handles>
            <message type="image" />
            <message type="voice" />
            <message type="video" />
            <message type="shortvideo" />
            <message type="location" />
            <message type="link" />
            <message type="subscribe" />
            <message type="qr" />
            <message type="trace" />
            <message type="click" />
            <message type="merchant_order" />
            <message type="text" />
        </handles>
        <supports>
            <item type="wxapp" />
            <item type="app" />
        </supports>

        <rule embed="true" />
        <card embed="false" />
    </platform>
    <bindings>
    </bindings>
    <permissions>
    </permissions>
    <install><![CDATA[<?= $generator->install ?>]]></install>
    <uninstall><![CDATA[<?= $generator->uninstall ?>]]></uninstall>
    <upgrade><![CDATA[<?= $generator->upgrade ?>]]></upgrade>
</manifest>