<?php

define( 'WP_ADMIN', true );
define( 'SHORTINIT', true );

require dirname($_SERVER['SCRIPT_FILENAME'], 2) . '/wp-load.php';
require ABSPATH . '/wp-content/plugins/wk/weikit.php';