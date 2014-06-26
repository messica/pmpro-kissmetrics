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
        'js' => '',
        'track_wp_registrations' => '',
        'track_wp_logins' => '',
        'track_pmpro_pages' => '',
        'track_pmpro_cancel' => '',
        'track_pmpro_change_level' => '',
        'track_pmpro_checkout' => '',
        'track_pmpro_trials' => ''
    );
}

/*
 * Requires
 */
require_once(plugin_dir_path(__FILE__) . '/includes/lib/km.php');   //KISSmetrics PHP Library
require_once(plugin_dir_path(__FILE__) . '/includes/init.php');     //plugin initialization

/*
 * Track WP Events
 */

//User registers
function pmprokm_user_register($user_id) {

    global $pmprokm_options;

    if(!defined('PMPROKM_READY') || empty($pmprokm_options['track_wp_registrations']))
        return;

    $user = get_userdata($user_id);
    $props = array(
        'WP Username' => $user->user_login,
        'Email Address' => $user->user_email,
        'Display Name' => $user->display_name,
        'User ID' => $user_id
    );

    KM::record('Registered', $props);
}
add_action('user_register', 'pmprokm_user_register');

//User logs in
function pmprokm_wp_login($user_login, $user) {

    global $pmprokm_options;

    if(!defined('PMPROKM_READY') || empty($pmprokm_options['track_wp_logins']))
        return;

    KM::record('Logged in');

}
add_action('wp_login', 'pmprokm_wp_login', 10, 2);

/*
 * Track PMPro Events
 */

//User visits any PMPro front end page
function pmprokm_template_redirect() {

    global $post, $pmpro_pages, $pmprokm_options;

    if(!defined('PMPROKM_READY') || empty($pmprokm_options['track_pmpro_pages']))
        return;

    if(in_array($post->ID, $pmpro_pages)) {

        $props = array(
            'page' => array_keys($pmpro_pages, $post->ID)[0],
            'permalink' => get_permalink($post->ID)
        );

        $event = 'Visited PMPro ' . ucfirst(array_keys($pmpro_pages, $post->ID)[0]) . ' Page';

        //include selected level if we're on che checkout page
        if($post->ID == $pmpro_pages['checkout']) {
            $level = pmpro_getLevel($_REQUEST['level']);
            $event .= ' (' . $level->name . ')';
        }

        //record event
        KM::record($event);
    }
}
add_action('template_redirect', 'pmprokm_template_redirect');

//User changes level or cancels
function pmprokm_after_change_membership_level($level_id, $user_id) {

    global $current_user, $pmprokm_options;

    if(!defined('PMPROKM_READY'))
        return;

    //switch KM identity to affected user
    $user = get_userdata($user_id);
    KM::identify($user->user_login);

    //user changed level
    if(!empty($pmprokm_options['track_pmpro_change_level']) && !empty($level_id)) {
        $level = pmpro_getLevel($level_id);
        KM::record('Membership Level Changed', array('Membership Level' => $level->name));
    }

    //user cancelled
    if(!empty($pmprokm_options['track_pmpro_cancel']) && pmpro_hasMembershipLevel(0, $user_id)) {
        KM::record('Membership Level Cancelled');
    }

}
add_action('pmpro_after_change_membership_level', 'pmprokm_after_change_membership_level', 10, 2);

//User checks out
function pmprokm_after_checkout($user_id) {

    global $current_user, $pmprokm_options;

    if(!defined('PMPROKM_READY'))
        return;

    if(!empty($pmprokm_options['track_pmpro_checkout'])) {
        $order = new MemberOrder;
        $order->getLastMemberOrder($user_id, 'success');
        $level = pmpro_getLevel($order->membership_id);

        $props = array(
            'Last Order ID' => $order->id
        );

        //track event
        KM::record('Checked Out', $props);
    }

    //if level contains trial, track trial as well
    if($level->trial_limit > 0 && !empty($pmprokm_options['track_pmpro_trials']))
        KM::record('Started Trial');
}
add_action('pmpro_after_checkout', 'pmprokm_after_checkout');



/*
 * Setup Settings Page
 */
function pmprokm_admin_menu() {
    add_options_page('PMPro KISSmetrics Settings', 'PMPro KISSmetrics', apply_filters('pmpro_edit_member_capability', 'manage_options'), 'pmpro-kissmetrics', 'pmprokm_settings_page');
}
add_action('admin_menu', 'pmprokm_admin_menu');

function pmprokm_admin_init() {

    //register setting
    register_setting('pmprokm_options', 'pmprokm_options');

    //add settings sections
    add_settings_section('pmprokm_general', 'General Settings', 'pmprokm_general', 'pmpro-kissmetrics');
    add_settings_section('pmprokm_wp', 'WordPress User Tracking', 'pmprokm_wp', 'pmpro-kissmetrics');

    if(defined('PMPRO_VERSION'))
        add_settings_section('pmprokm_pmpro', 'Paid Memberships Pro Tracking', 'pmprokm_pmpro', 'pmpro-kissmetrics');

    //add settings fields
    add_settings_field('apikey', 'API Key', 'pmprokm_apikey', 'pmpro-kissmetrics', 'pmprokm_general');
    add_settings_field('js', 'JavaScript Tracking Code', 'pmprokm_js', 'pmpro-kissmetrics', 'pmprokm_general');
    add_settings_field('track_wp_registrations', 'Track Registrations', 'pmprokm_track_wp_registrations', 'pmpro-kissmetrics', 'pmprokm_wp');
    add_settings_field('track_wp_logins', 'Track Logins', 'pmprokm_track_wp_logins', 'pmpro-kissmetrics', 'pmprokm_wp');

    if(defined('PMPRO_VERSION')) {
        add_settings_field('track_pmpro_pages', 'Track PMPro Front End Page Visits', 'pmprokm_track_pmpro_pages', 'pmpro-kissmetrics', 'pmprokm_pmpro');
        add_settings_field('track_pmpro_change_level', 'Track Membership Level Changes', 'pmprokm_track_pmpro_change_level', 'pmpro-kissmetrics', 'pmprokm_pmpro');
        add_settings_field('track_pmpro_cancel', 'Track Membership Cancellations', 'pmprokm_track_pmpro_cancel', 'pmpro-kissmetrics', 'pmprokm_pmpro');
        add_settings_field('track_pmpro_checkout', 'Track Membership Checkouts', 'pmprokm_track_pmpro_checkout', 'pmpro-kissmetrics', 'pmprokm_pmpro');
        add_settings_field('track_pmpro_trials', 'Track Membership Trials', 'pmprokm_track_pmpro_trials', 'pmpro-kissmetrics', 'pmprokm_pmpro');
    }

}
add_action('admin_init', 'pmprokm_admin_init');

function pmprokm_settings_page() {
    require_once(plugin_dir_path(__FILE__) . '/adminpages/settings.php');
}