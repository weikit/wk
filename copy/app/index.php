<?php
define( 'IN_MOBILE', true );
define( 'SHORTINIT', true );

require dirname($_SERVER['SCRIPT_FILENAME'], 2) . '/wp-load.php';
require ABSPATH . 'wp-content/plugins/wk/weikit.php';