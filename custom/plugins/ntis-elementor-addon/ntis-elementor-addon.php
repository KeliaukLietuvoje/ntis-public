<?php
/**
 * Plugin Name:       NTIS Elementor addons
 * Plugin URI:        https://www.ntis.lt
 * Description:       Elementor funkcijų išplėtimas
 * Version:           1.0.0
 * Author:            Petras Pauliūnas
 * Author URI:        mailto:petras.pauliunas@gmail.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ntis
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (defined('NTIS_VERSION') || ! defined('ABSPATH')) {
    return;
}

define('NTIS_VERSION', '1.0.0');
define('NTIS_BUILT', 100);
define('NTIS_FILE', __FILE__);
define('NTIS_DIR', plugin_dir_path(__FILE__));
define('NTIS_URI', plugin_dir_url(__FILE__));
define('NTIS_SLUG', basename(NTIS_DIR) . '/' . basename(__FILE__));

function ntis($subclass = null)
{
    global $ntis;

    $args     = func_get_args();
    $subclass = array_shift($args);

    if (is_null($subclass) || ! is_string($subclass)) {
        return $ntis;
    }

    return call_user_func_array(array( $ntis, $subclass ), $args);
}

require NTIS_DIR . 'classes/ntis.class.php';

global $ntis;
$ntis = new NTIS();
