<?php
/*
Plugin Name: CleanUp
Description: The fastest, cleanest way to get rid of the parts of WordPress you don't need.
Version: 2.2.0
Author: Petras Pauliūnas
Author URI: mailto:petras.pauliunas@gmail.com
License: GPLv2
Text Domain: cleanup
Domain Path: /i18n/languages/
*/


// Don't load directly
if (!defined('ABSPATH')) {
    exit;
}


// Load includes
require_once(plugin_dir_path(__FILE__) . 'functions.php');


// Load plugin classes
require_once(plugin_dir_path(__FILE__) . 'class-cleanup.php');


// Instantiate
add_action('plugins_loaded', function () {
    global $cleanup;
    $cleanup = new Cleanup();
});


// Load text domain for translations
add_action('plugins_loaded', function () {
    load_plugin_textdomain('cleanup', false, basename(plugin_dir_path(__FILE__)) . '/i18n/languages/');
});


// Flush rewrite rules on activation
register_activation_hook(__FILE__, function () { flush_rewrite_rules(); });


// Install/upgrade
register_activation_hook(__FILE__, 'cleanup_install');
add_action('plugins_loaded', function () {
    if (get_option('cleanup_version') != @Cleanup::VERSION) {
        cleanup_install();
    }
});


// Plugin installation
function cleanup_install()
{
    global $wpdb;

    // Update version
    update_option('cleanup_version', @Cleanup::VERSION);

    // Update settings
    if (version_compare(@Cleanup::VERSION, '1.4.0', '>=')) {
        if (get_option('cleanup_xmlrpc_disabled', null) !== null && get_option('cleanup_xmlrpc_enabled')) {
            update_option('cleanup_xmlrpc_disabled', get_option('cleanup_xmlrpc_enabled'));
            delete_option('cleanup_xmlrpc_enabled');
        }
        if (get_option('cleanup_login_replace_wp_logo_link', null) !== null && get_option('cleanup_login_remove_wp_logo')) {
            update_option('cleanup_login_replace_wp_logo_link', get_option('cleanup_login_remove_wp_logo'));
            delete_option('cleanup_login_remove_wp_logo');
        }
    }
}
