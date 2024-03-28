<?php

if (!defined('ABSPATH')) {
    exit;
}

define('NTIS_THEME_DIR', get_template_directory());
define('NTIS_THEME_URL', get_template_directory_uri());

define('NTIS_VERSION', '1.0');

if (! function_exists('ntis_setup')) {
    /**
     * Set up theme support.
     *
     * @return void
     */
    function ntis_setup()
    {
        load_theme_textdomain('ntis', get_template_directory() . '/languages');

        register_nav_menus([ 'menu-1' => __('Header', 'ntis') ]);

        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support(
            'custom-logo',
            [
                'height'      => 100,
                'width'       => 350,
                'flex-height' => true,
                'flex-width'  => true,
            ]
        );
    }
}
add_action('after_setup_theme', 'ntis_setup');

add_filter('wp_sitemaps_add_provider', function ($provider, $name) {
    return ($name == 'users') ? false : $provider;
}, 10, 2);

if (! function_exists('ntis_scripts_styles')) {
    function ntis_scripts_styles()
    {
        wp_enqueue_style('font-ntis', NTIS_THEME_URL . '/inc/elementor/css/ntis-custom.css', array(), NTIS_VERSION);

        wp_enqueue_style(
            'ntis',
            get_template_directory_uri() . '/style.css',
            [],
            NTIS_VERSION
        );
    }
}
add_action('wp_enqueue_scripts', 'ntis_scripts_styles');

function add_favicon_meta()
{
    ?>
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo NTIS_THEME_URL;?>/assets/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php echo NTIS_THEME_URL;?>/assets/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <?php
}

add_action('wp_head', 'add_favicon_meta');

/*
* Copyright
* Usage: [copyright year="2024"]
*/
function copyright_shortcode($atts)
{

    // Attributes
    $atts = shortcode_atts(
        array(
            'year' => '2024',
        ),
        $atts,
        'copyright'
    );

    if(date('Y') == $atts['year']) {
        return '&copy; ' . $atts['year'];
    } else {
        return '&copy; ' . $atts['year'] . '-' . date('Y');
    }

}
add_shortcode('copyright', 'copyright_shortcode');


function ntis_tab_shortcode($atts)
{
    // Attributes
    $atts = shortcode_atts(
        array(
            'color' => 'black',
            'count' => '2023',
            'href' => '#',
        ),
        $atts,
        'ntis_tab'
    );
    return '<div class="ntis-tab"><a href="' . $atts['href'] . '" class="ntis-link"><span class="ntis-label">' . $atts['count'] . '</span><span class="ntis-arrow arrow-' . $atts['color'] . '"></span></a></div>';
}
add_shortcode('ntis_tab', 'ntis_tab_shortcode');


function is_dir_empty($dir)
{
    if (!is_readable($dir)) {
        return null; // when the directory is unreadable
    }
    return (count(glob("$dir/*")) === 0);
}

// Automatically purge and regenerate the Elementor CSS cache
add_action('init', 'clear_elementor_cache');
function clear_elementor_cache()
{
    if (is_dir_empty($_SERVER['DOCUMENT_ROOT'] . '/wpapp/uploads/elementor/css')) {
        if (! did_action('elementor/loaded')) {
            return;
        }
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
}
if (!function_exists('ntis_elementor_is_activated')) {
    function ntis_elementor_is_activated()
    {
        if(function_exists('elementor_load_plugin_textdomain')) {
            return true;
        } else {
            return false;
        }
    }
}
if (! function_exists('ntis_elementor_is_edit_mode')) {
    function ntis_elementor_is_edit_mode()
    {
        if(!ntis_elementor_is_activated()) {
            return false;
        }

        return Elementor\Plugin::$instance->editor->is_edit_mode();
    }
}
if (! function_exists('ntis_elementor_is_preview_mode')) {
    function ntis_elementor_is_preview_mode()
    {
        if(!ntis_elementor_is_activated()) {
            return false;
        }

        return Elementor\Plugin::$instance->preview->is_preview_mode();
    }
}

if (! function_exists('ntis_elementor_is_preview_page')) {
    function ntis_elementor_is_preview_page()
    {
        return isset($_GET['preview_id']);
    }
}
if(ntis_elementor_is_activated()) {
    require_once NTIS_THEME_DIR .'/inc/class-elementor.php';
}
if(!function_exists('ntis_sitemap')) {
    require_once NTIS_THEME_DIR .'/inc/shortcodes/sitemap/sitemap.php';
}
