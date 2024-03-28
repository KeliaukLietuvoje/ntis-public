<?php

/**
 * Plugin Name: WordPress Sentry
 * Plugin URI: https://github.com/stayallive/wp-sentry
 * Description: A (unofficial) WordPress plugin to report PHP and JavaScript errors to Sentry.
 * Version: must-use-proxy
 * Author: Alex Bouma
 * Author URI: https://alex.bouma.dev
 * License: MIT
 */

$wp_sentry = WP_CONTENT_DIR . '/plugins/wp-sentry-integration/wp-sentry.php';

// Do not crash in case the plugin is not installed
if (! file_exists($wp_sentry)) {
    return;
}

require $wp_sentry;
