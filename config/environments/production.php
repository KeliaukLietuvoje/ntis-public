<?php
/**
 * Configuration overrides for WP_ENV === 'development'
 */

use Roots\WPConfig\Config;

use function Env\env;

Config::define('DISALLOW_INDEXING', false);
// Enable plugin and theme updates and installation from the admin
Config::define('DISALLOW_FILE_MODS', false);
Config::define('WP_MEMORY_LIMIT', '512M');

/**
 * Sentry init
 */
Config::define('WP_SENTRY_PHP_DSN', env('SENTRY_DSN'));
Config::define('WP_SENTRY_ENV', 'production');
Config::define('WP_SENTRY_ERROR_TYPES', E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
