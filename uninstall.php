<?php

defined('ABSPATH') || exit;

require_once ( __DIR__ . '/weikit.php' );

do_action('wk_init');

Yii::createObject(WeikitService::class)->uninstall();