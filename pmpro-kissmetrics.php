<?php

/**
 * Plugin Name: PMPro KISSmetrics
 * Description: Integrates PMPro with KISSmetrics to track user activity.
 * Author: Stranger Studios
 * Author URI: http://strangerstudios.com
 * Version: .1
 */

/*
 * Globals
 */
global $pmprokm_options;

$pmprokm_options = get_option('pmprokm_options');
if(empty($pmprokm_options)) {
    $pmprokm_options = array(
        'apikey' => '',
        'js' => ''
    );
}

/*
 * Requires
 */
require_once(plugin_dir_path(__FILE__) . '/includes/lib/km.php');
require_once(plugin_dir_path(__FILE__) . '/includes/init.php');

/*
 * Track Events
 */
