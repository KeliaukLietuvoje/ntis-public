<?php

// Don't load directly
if (!defined('ABSPATH')) {
    exit;
}


// Admin functions

function cleanup_group_functions()
{
    global $cleanup;
    $output = array();
    foreach ((array)$cleanup->function_details as $fn => $item) {
        $output[$item['group']][$fn] = $item;
    }
    foreach (array_keys((array)$output) as $group) {
        uasort($output[$group], 'cleanup_group_functions_sort_callback');
    }
    ksort($output);
    return $output;
}

function cleanup_hide_wp_version()
{
    add_filter('script_loader_src', 'remove_script_version', 15, 1);
    add_filter('style_loader_src', 'remove_script_version', 15, 1);
}

function remove_script_version($src)
{
    if (strpos($src, 'ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

function cleanup_remove_dashicons()
{
    if(!is_user_logged_in()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}

function cleanup_group_functions_sort_callback($a, $b)
{
    return strnatcmp($a['title'], $b['title']);
}


// Hook functions

function cleanup_admin_bar_logout_link()
{
    add_action('admin_bar_menu', 'cleanup_admin_bar_logout_link_admin_bar_menu_callback', 1);
    add_action('admin_head', 'cleanup_admin_bar_logout_link_admin_head_callback');
}

function cleanup_admin_bar_logout_link_admin_bar_menu_callback($wp_admin_bar)
{
    $wp_admin_bar->add_node(array(
        'href' => wp_logout_url(),
        'id' => 'cleanup-logout',
        'meta' => array(
            'class' => 'cleanup-important',
        ),
        'parent' => 'top-secondary',
        'title' => __('Log Out'),
    ));
}

function cleanup_admin_bar_logout_link_admin_head_callback()
{
    global $_wp_admin_css_colors;
    $user_option_admin_color = get_user_option('admin_color');
    $admin_color_scheme = $_wp_admin_css_colors[$user_option_admin_color];
    // "Modern" only has 3 colors
    if ($user_option_admin_color == 'modern' || empty($admin_color_scheme->colors[3])) {
        $logout_button_color = $admin_color_scheme->colors[1];
        $logout_button_hover_color = $admin_color_scheme->colors[2];
        $logout_button_hover_text_color = '#000000';
    }
    // Most schemes have 4 colors
    else {
        $logout_button_color = $admin_color_scheme->colors[2];
        $logout_button_hover_color = $admin_color_scheme->colors[3];
    }
    ?>
	<style>
		#wpadminbar .cleanup-important {
			background: <?php echo esc_attr($logout_button_color); ?>;
		}
		#wpadminbar .cleanup-important:hover {
			background: <?php echo esc_attr($logout_button_hover_color); ?>;
		}
		<?php
        if (!empty($logout_button_hover_text_color)) {
            ?>
			#wpadminbar .cleanup-important:hover .ab-item, #wpadminbar .cleanup-important .ab-item:hover {
				color: <?php echo esc_attr($logout_button_hover_text_color); ?> !important;
			}
			<?php
        }
    ?>
	</style>
	<?php
}

function cleanup_auto_core_update_send_email_only_on_error($send, $type, $core_update, $result)
{
    return (empty($type) || $type != 'success');
}

function cleanup_core_upgrade_skip_new_bundled()
{
    if (!defined('CORE_UPGRADE_SKIP_NEW_BUNDLED')) {
        define('CORE_UPGRADE_SKIP_NEW_BUNDLED', true);
    }
}

function cleanup_disable_site_search()
{
    if (!is_admin()) {
        add_action('parse_query', 'cleanup_disable_site_search_parse_query_callback', 5);
    }
    add_filter('get_search_form', '__return_false', 999);
    add_action('widgets_init', 'cleanup_disable_site_search_widgets_init_callback', 1);
}

function cleanup_disable_site_search_parse_query_callback($query)
{
    if ($query->is_search && $query->is_main_query()) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
}

function cleanup_disable_site_search_widgets_init_callback()
{
    unregister_widget('WP_Widget_Search');
}

function cleanup_disallow_file_edit()
{
    if (!defined('DISALLOW_FILE_EDIT')) {
        define('DISALLOW_FILE_EDIT', true);
    }
}

function cleanup_disallow_full_site_editing()
{
    add_action('admin_bar_menu', 'cleanup_remove_edit_site', 999, 1);
    add_action('admin_menu', 'cleanup_disallow_full_site_editing_admin_menu_callback');
    add_action('current_screen', 'cleanup_disallow_full_site_editing_current_screen_callback');
    add_action('customize_controls_head', 'cleanup_disallow_full_site_editing_customize_controls_head_callback');
}

function cleanup_disallow_full_site_editing_admin_menu_callback()
{
    remove_submenu_page('themes.php', 'site-editor.php');
}

function cleanup_disallow_full_site_editing_current_screen_callback()
{
    global $pagenow;
    if (is_admin() && 'site-editor.php' === $pagenow) {
        wp_redirect(admin_url('/'));
        exit;
    }
}

function cleanup_disallow_full_site_editing_customize_controls_head_callback()
{
    // @todo Find a better way to do this.
    echo '<style>.notice[data-code="site_editor_block_theme_notice"]{display:none!important;}</style>';
}

function cleanup_hide_admin_bar_for_logged_in_non_editors()
{
    if (!wp_doing_ajax() && is_user_logged_in() && !current_user_can('edit_posts')) {
        add_filter('show_admin_bar', '__return_false');
    }
}

function cleanup_limit_admin_elements_for_logged_in_non_editors()
{
    if (!wp_doing_ajax() && is_admin() && is_user_logged_in() && !current_user_can('edit_posts')) {
        remove_menu_page('index.php');
        add_filter('admin_footer_text', '__return_false');
        add_filter('update_footer', '__return_false', 99);
    }
}

function cleanup_remove_jquery_migrate($scripts)
{
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $script = $scripts->registered['jquery'];

        if (!empty($script->deps)) {
            $script->deps = array_diff($script->deps, array( 'jquery-migrate' ));
        }
    }
}

function cleanup_login_replace_wp_logo_link()
{
    if (has_site_icon()) {
        ?>
		<style type="text/css">.login h1 a { background-image: url('<?php echo get_site_icon_url(192); ?>') !important; border-radius: 16px; }</style>
		<?php
    } else {
        ?>
		<style type="text/css">.login h1 a { display: none !important; }</style>
		<?php
    }
    add_filter('login_headerurl', 'cleanup_login_replace_wp_logo_link_login_headerurl_callback');
}

function cleanup_login_replace_wp_logo_link_login_headerurl_callback()
{
    return home_url('/');
}

function cleanup_redirect_admin_to_homepage_for_logged_in_non_editors()
{
    if (!wp_doing_ajax() && is_admin() && is_user_logged_in() && !current_user_can('edit_posts')) {
        global $pagenow;
        $options = get_option('cleanup_redirect_admin_to_homepage_for_logged_in_non_editors_options');
        if ($pagenow != 'profile.php' || !empty($options['prevent_profile_access'])) {
            wp_redirect(home_url('/'));
            exit;
        }
    }
}

function cleanup_remove_admin_color_scheme_picker()
{
    remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
}

function cleanup_remove_admin_wp_logo($wp_admin_bar)
{
    $wp_admin_bar->remove_node('wp-logo');
}

function cleanup_remove_comments_column($columns)
{
    unset($columns['comments']);
    return $columns;
}

function cleanup_remove_comments_from_admin()
{
    add_action('admin_menu', 'cleanup_remove_comments_from_admin_admin_menu_callback');
    add_action('admin_bar_menu', 'cleanup_remove_comments_from_admin_admin_bar_menu_callback', 999);
    add_filter('manage_edit-post_columns', 'cleanup_remove_comments_column');
    add_filter('manage_edit-page_columns', 'cleanup_remove_comments_column');
    add_filter('manage_media_columns', 'cleanup_remove_comments_column');
    add_filter('wp_headers', 'cleanup_filter_wp_headers');
    add_filter('xmlrpc_methods', 'cleanup_disable_xmlrc_comments');
    add_filter('rest_pre_insert_comment', 'cleanup_disable_rest_API_comments');
    add_filter('rest_endpoints', 'cleanup_filter_rest_endpoints_comments');
    // Redirect any user trying to access comments page
    global $pagenow;

    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }
}
function cleanup_filter_rest_endpoints_comments($endpoints)
{
    unset($endpoints['comments']);
    return $endpoints;
}
function cleanup_disable_rest_API_comments($prepared_comment, $request)
{
    return;
}
function cleanup_disable_xmlrc_comments($methods)
{
    unset($methods['wp.newComment']);
    return $methods;
}
function cleanup_filter_wp_headers($headers)
{
    unset($headers['X-Pingback']);
    return $headers;
}

function cleanup_remove_comments_from_admin_admin_menu_callback()
{
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'core');
}

function cleanup_remove_comments_from_admin_admin_bar_menu_callback($wp_admin_bar)
{
    $wp_admin_bar->remove_node('comments');
}

function cleanup_remove_comments_from_front_end()
{
    if (!is_admin()) {
        add_filter('comments_array', function () { return array(); });
        add_filter('comments_open', '__return_false');
        add_filter('pings_open', '__return_false');
        add_action('comment_form_comments_closed', function () { echo '<style>.nocomments { display: none !important; }</style>'; });
    } else {
        add_action('admin_menu', function () {
            remove_menu_page('edit-comments.php');
        });
        // Disable support for comments and trackbacks in post types
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }
}

function cleanup_remove_dashboard_widgets()
{
    if ($options = get_option('cleanup_remove_dashboard_widgets_options')) {
        foreach ((array)$options as $option => $bool) {
            if (!empty($bool)) {
                if ($option == 'welcome_panel') {
                    remove_action('welcome_panel', 'wp_welcome_panel');
                } else {
                    remove_meta_box($option, 'dashboard', 'core');
                }
            }
        }
    }
}

function cleanup_remove_default_block_patterns()
{
    remove_theme_support('core-block-patterns');
}

function cleanup_remove_duotone_svg_filters()
{
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
    remove_action('in_admin_header', 'wp_global_styles_render_svg_filters');
}

function cleanup_remove_edit_site($wp_admin_bar)
{
    $wp_admin_bar->remove_node('site-editor');
}

function cleanup_remove_head_tags()
{
    if ($options = get_option('cleanup_remove_head_tags_options')) {
        foreach ((array)$options as $option => $bool) {
            if (!empty($bool)) {
                switch ($option) {
                    case 'feed_links':
                        remove_action('wp_head', 'feed_links', 2);
                        remove_action('wp_head', 'feed_links_extra', 3);
                        break;
                    case 'oembed_linktypes':
                        add_filter('oembed_discovery_links', '__return_false');
                        break;
                    case 'resource_hints':
                    case 'wp_resource_hints':
                        remove_action('login_head', 'wp_resource_hints', 2);
                        remove_action('wp_head', 'wp_resource_hints', 2);
                        break;
                    case 'rest_output_link_wp_head':
                        remove_action('template_redirect', 'rest_output_link_header', 11);
                        remove_action('wp_head', 'rest_output_link_wp_head');
                        remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
                        break;
                    case 'wp_shortlink_wp_head':
                        remove_action('template_redirect', 'wp_shortlink_header', 11);
                        remove_action('wp_head', 'wp_shortlink_wp_head');
                        break;
                    default:
                        remove_action('wp_head', $option);
                        break;
                }
            }
        }
    }
}

function cleanup_remove_howdy($wp_admin_bar)
{
    $my_account = $wp_admin_bar->get_node('my-account');
    $wp_admin_bar->add_node(array(
        'id' => 'my-account',
        'title' => substr($my_account->title, strpos($my_account->title, '<span class="display-name">')),
    ));
}

function cleanup_remove_posts_from_admin()
{
    add_action('admin_bar_menu', 'cleanup_remove_posts_from_admin_admin_bar_menu_callback', 999);
    add_action('admin_menu', 'cleanup_remove_posts_from_admin_admin_menu_callback');
}

function cleanup_remove_posts_from_admin_admin_bar_menu_callback($wp_admin_bar)
{
    $wp_admin_bar->remove_node('new-post');
}

function cleanup_remove_posts_from_admin_admin_menu_callback()
{
    remove_menu_page('edit.php');
}

function generate_dynamic_nonce()
{
    return wp_create_nonce(NONCE_KEY);
}

function add_nonce_to_csp($headers)
{
    $nonce = generate_dynamic_nonce();
    $csp_directive = "script-src 'self' 'unsafe-inline' 'unsafe-eval' ;";
    if (isset($headers['Content-Security-Policy'])) {
        $headers['Content-Security-Policy'] = str_replace("script-src", $csp_directive, $headers['Content-Security-Policy']);
    } else {
        $headers['Content-Security-Policy'] = $csp_directive;
    }
    return $headers;
}

function cleanup_add_httponly_to_cookies()
{
    if (headers_sent()) {
        return;
    }
    foreach (headers_list() as $header) {
        if (0 === stripos($header, 'Set-Cookie:')) {
            /* this header is a cookie! */
            if (false === stripos($header, 'HttpOnly')) {
                /* no HttpOnly item, append it */
                $header .= '; HttpOnly';
                /* replace the cookie in the list of headers */
                header($header, true);
            }
        }
    }
}
function add_nonce_to_script_tag($tag, $handle)
{
    if (strpos($tag, 'script') !== false && strpos($tag, 'nonce') === false) {
        $nonce = generate_dynamic_nonce();
        $tag = str_replace('<script', "<script nonce=\"$nonce\"", $tag);
    }

    return $tag;
}

function cleanup_add_csp_headers()
{
    // csp doesnt support Elementor yet
    //add_filter('wp_headers', 'add_nonce_to_csp');
    //add_filter( 'script_loader_tag', 'add_nonce_to_script_tag',10,2 );
}

function cleanup_remove_gutenberg_block_library()
{
    add_filter('use_block_editor_for_post_type', '__return_false', 10);
    add_action('wp_enqueue_scripts', 'remove_block_css', 100);
}
function remove_block_css()
{
    wp_dequeue_style('wp-block-library'); // Wordpress core
    wp_dequeue_style('wp-block-library-theme'); // Wordpress core
    wp_dequeue_style('wc-block-style'); // WooCommerce
    wp_dequeue_style('storefront-gutenberg-blocks'); // Storefront theme
}

function cleanup_remove_widgets_block_editor()
{
    remove_theme_support('widgets-block-editor');
}

function cleanup_remove_sitemaps()
{
    // Disable sitemaps
    add_filter('wp_sitemaps_enabled', '__return_false');
    add_action('init', function () {
        remove_action('init', 'wp_sitemaps_get_server');
    }, 5);
}

function cleanup_remove_wp_emoji()
{
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    // Remove WP emoji from TinyMCE
    add_filter('tiny_mce_plugins', 'cleanup_remove_wp_emoji_from_tinymce', 10, 1);
    // Remove WP emoji DNS prefetch
    add_filter('emoji_svg_url', '__return_false');
}

function cleanup_allow_svg_mime($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
function cleanup_svg_support()
{
    add_filter('upload_mimes', 'cleanup_allow_svg_mime');
    add_filter('as3cf_allowed_mime_types', 'cleanup_allow_svg_mime');
}

function cleanup_remove_wp_emoji_from_tinymce($plugins)
{
    return is_array($plugins) ? array_diff($plugins, array('wpemoji')) : array();
}

// This plugin's name appears contradictory but it is kept this way for backwards compatibility
function cleanup_xmlrpc_disabled()
{
    $options = get_option('cleanup_xmlrpc_disabled_options');
    // Turn off XML-RPC (after WP 3.5 this only turns off unauthenticated access)
    add_filter('xmlrpc_enabled', '__return_false');
    // Silently kill any XML-RPC request
    if (!empty($options['kill_requests'])) {
        if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
            status_header(403);
            exit;
        }
    }
}


// Utility functions

function cleanup_deactivate_and_delete_hello_dolly()
{
    $return = false;
    if (is_plugin_active('hello-dolly/hello.php')) {
        deactivate_plugins(array('hello-dolly/hello.php'));
    } elseif (is_plugin_active('hello.php')) {
        deactivate_plugins(array('hello.php'));
    }
    if (array_key_exists('hello-dolly/hello.php', get_plugins())) {
        delete_plugins(array('hello-dolly/hello.php'));
        $return = true;
    } elseif (array_key_exists('hello.php', get_plugins())) {
        delete_plugins(array('hello.php'));
        $return = true;
    }
    return $return;
}

/**
 * This function is no longer automatically called back in
 * cleanup_deactivate_and_delete_hello_dolly() above, but is
 * retained for future use and is not deprecated.
 */
function cleanup_deactivate_and_delete_hello_dolly_admin_head_callback()
{
    $current_screen = get_current_screen();
    if ($current_screen->base == 'plugin-install') {
        ?>
		<style>.plugin-card-hello-dolly { display: none !important; }</style>
		<?php
    }
}

function cleanup_delete_sample_content()
{
    $return = false;
    if (wp_delete_comment(1)) {
        $return = true;
    } // Sample comment by "A WordPress Commenter" on "Hello world!" post
    if (wp_delete_post(1, true)) {
        $return = true;
    } // "Hello world!" post
    if (wp_delete_post(2, true)) {
        $return = true;
    } // "Sample Page" page
    return $return;
}

function cleanup_remove_default_tagline()
{
    $return = false;
    if (get_option('blogdescription') == __('Just another WordPress site', 'no-nonsense')) {
        if (update_option('blogdescription', '')) {
            $return = true;
        }
    }
    return $return;
}

function cleanup_set_permalink_structure_to_postname()
{
    $return = false;
    if (update_option('permalink_structure', '/%postname%/')) {
        flush_rewrite_rules();
        $return = true;
    }
    return $return;
}
