<?php

// Don't load directly
if (!defined('ABSPATH')) { exit; }

class Cleanup {

	const NAME = 'Cleanup';
	const VERSION = '2.2.0';
	
	public $function_details = array();
	public $utility_details = array();

	public function __construct() {
	
		// Function details
		$this->function_details = array(
			
			'cleanup_admin_bar_logout_link' => array(
				'title' => __('Admin bar logout link', 'cleanup'),
				'description' => __('Adds a color-highlighted logout link directly into the admin bar, next to the username. Helpful to remind users to log out when their session is done.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Admin Bar', 'cleanup'),
			),
			
			'cleanup_auto_core_update_send_email_only_on_error' => array(
				'title' => __('Auto core update send email only on error', 'cleanup'),
				'description' => __('By default, site admins receive a notification email every time WordPress runs auto-updates. Turn this on to only receive emails if there is an error during the update process.', 'cleanup'),
				'hook_type' => 'filter',
				'hook' => 'auto_core_update_send_email',
				'priority' => 10,
				'pn' => 4,
				'group' => __('Security and Updates', 'cleanup'),
			),
			
			'cleanup_disable_site_search' => array(
				'title' => __('Disable site search', 'cleanup'),
				'description' => __('If your site does not need search functionality, turn this on to cause all standard WordPress search URLs to redirect to the home page without performing a search. Does not affect admin search functionality. Also deregisters the search widget.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Front End', 'cleanup'),
			),

			'cleanup_remove_sitemaps' => array(
				'title' => __('Disable sitemaps', 'cleanup'),
				'description' => __('If your site does not need default sitemap functionality, turn this on to cause all standard WordPress sitemaps to be disabled.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Front End', 'cleanup'),
			),

			'cleanup_remove_jquery_migrate' => array(
				'title' => __('Remove jQuery migrate', 'cleanup'),
				'description' => __('Most up-to-date frontend code and plugins don’t require jquery-migrate.min.js. In most cases, this simply adds unnecessary load to your site.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'wp_default_scripts',
				'priority' => 10,
				'pn' => 1,
				'group' => __('Front End', 'cleanup'),
			),

			'cleanup_disallow_full_site_editing' => array(
				'title' => __('Disallow full site editing (FSE)', 'cleanup'),
				'description' => sprintf(__('Removes the "Edit site" link in the admin bar, the "Editor" link under "Appearance," and the FSE notice in the Customizer. Also redirects any direct attempts to access the FSE page to the admin dashboard. If this option is active, you do not need to use the %1$sRemove "Edit site" link%2$s option under "Admin Bar."', 'cleanup'), '<strong>', '</strong>'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Block Editor', 'cleanup'),
			),
			
			'cleanup_hide_admin_bar_for_logged_in_non_editors' => array(
				'title' => __('Hide admin bar for logged-in non-editors', 'cleanup'),
				'description' => sprintf(__('Hides the admin bar on front-end pages for logged-in users with no editing capabilities. Admin bar will still display for these users when they access their profile page. %1$sNote:%2$s With this option turned on, you will need to provide another way on the front end of your site for logged-in users to access their profile page and the logout link.', 'cleanup'), '<strong>', '</strong>'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Admin Bar', 'cleanup'),
			),

			'cleanup_limit_admin_elements_for_logged_in_non_editors' => array(
				'title' => __('Limit admin elements for logged-in non-editors', 'cleanup'),
				'description' => __('Hides parts of the admin sidebar menu and WordPress footer from logged-in users with no editing capabilities.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'admin_menu',
				'priority' => 11,
				'pn' => 0,
				'group' => __('Admin Access', 'cleanup'),
			),
			
			'cleanup_login_replace_wp_logo_link' => array(
				'title' => __('Replace WP logo with site icon on login screen', 'cleanup'),
				'description' => __('Replaces the WordPress logo and link on the login screen with the designated site icon (if set) and site link. If no icon is present, the WP logo and link are simply removed.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'login_enqueue_scripts',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Login', 'cleanup'),
			),

			'cleanup_redirect_admin_to_homepage_for_logged_in_non_editors' => array(
				'title' => __('Redirect admin to home page for logged-in non-editors', 'cleanup'),
				'description' => __('Logged-in users with no editing capabilities (e.g. Subscribers) will be redirected to the site home page if they try to access any admin pages, other than their own profile page.', 'cleanup'),
				'options' => array(
					'prevent_profile_access' => __('Also prevent access to profile screen', 'cleanup'),
				),
				'hook_type' => 'action',
				'hook' => 'admin_init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Admin Access', 'cleanup'),
			),

			'cleanup_remove_admin_color_scheme_picker' => array(
				'title' => __('Remove admin color scheme picker', 'cleanup'),
				'description' => __('Removes the color scheme picker from the user profile page.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'admin_init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Admin Features', 'cleanup'),
			),
			
			'cleanup_remove_admin_email_check_interval' => array(
				'title' => __('Remove admin email check interval', 'cleanup'),
				'description' => __('Skips the periodic verification of admin email address upon login.', 'cleanup'),
				'hook_type' => 'filter',
				'hook' => 'admin_email_check_interval',
				'priority' => 10,
				'pn' => 0,
				'cb' => '__return_false',
				'group' => __('Login', 'cleanup'),
			),

			'cleanup_remove_admin_wp_logo' => array(
				'title' => __('Remove admin bar WordPress logo', 'cleanup'),
				'description' => __('Removes WordPress icon and link from the admin bar.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'admin_bar_menu',
				'priority' => 11,
				'pn' => 1,
				'group' => __('Admin Bar', 'cleanup'),
			),

			'cleanup_remove_comments_from_admin' => array(
				'title' => __('Remove Comments from admin', 'cleanup'),
				'description' => sprintf(__('Removes links to Comments in the admin bar and admin sidebar menu. Does not actually deactivate comment functionality; this should be done under %1$sSettings %2$s Discussion%3$s.', 'cleanup'), '<a href="' . admin_url('options-discussion.php') . '" target="_blank">', '&gt;', '</a>'),
				'hook_type' => 'action',
				'hook' => 'admin_init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Admin Features', 'cleanup'),
			),
			
			'cleanup_remove_comments_from_front_end' => array(
				'title' => __('Remove comments from front end', 'cleanup'),
				'description' => __('Removes all standard comment output from front-end pages. May not function properly if theme uses non-standard methods to display comments.', 'cleanup'),
				'hook_type' => 'filter',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Front End', 'cleanup'),
			),
			
			'cleanup_remove_dashboard_widgets' => array(
				'title' => __('Remove Dashboard widgets', 'cleanup'),
				'description' => __('Removes the selected widgets from the WordPress admin dashboard.', 'cleanup'),
				'options' => array(
					'dashboard_activity' => __('Activity', 'cleanup'),
					'dashboard_right_now' => __('At a Glance', 'cleanup'),
					'dashboard_incoming_links' => __('Incoming Links', 'cleanup'),
					'dashboard_plugins' => __('Plugins', 'cleanup'),
					'dashboard_quick_press' => __('Quick Draft', 'cleanup'),
					'dashboard_recent_comments' => __('Recent Comments', 'cleanup'),
					'dashboard_recent_drafts' => __('Recent Drafts', 'cleanup'),
					//'dashboard_secondary' => __('Secondary', 'cleanup'), // Deprecated as of WP 3.8
					'dashboard_site_health' => __('Site Health', 'cleanup'),
					'welcome_panel' => __('Welcome', 'cleanup'),
					'dashboard_primary' => __('WordPress Events and News', 'cleanup'),
				),
				'hook_type' => 'action',
				'hook' => 'admin_init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Admin Features', 'cleanup'),
			),
			
			'cleanup_remove_default_block_patterns' => array(
				'title' => __('Remove default block patterns', 'cleanup'),
				'description' => sprintf(__('Removes the default block patterns from the block editor, leaving only custom block patterns defined by your theme.', 'cleanup'), '<strong>', '</strong>'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 9,
				'pn' => 0,
				'group' => __('Block Editor', 'cleanup'),
			),
			
			'cleanup_remove_duotone_svg_filters' => array(
				'title' => __('Remove duotone SVG filters', 'cleanup'),
				'description' => __('Removes hardcoded HTML SVG tags for block editor duotone effects that normally get loaded on every page for Safari users.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'after_setup_theme',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Block Editor', 'cleanup'),
			),
			
			'cleanup_remove_edit_site' => array(
				'title' => __('Remove "Edit site" link', 'cleanup'),
				'description' => sprintf(__('Removes the full site editing (FSE) link that appears in the admin bar on sites that use block themes, to avoid accidentally clicking it when intending to click "Edit Page/Post," but leaves other FSE features in place. To disallow FSE entirely, select %1$sDisallow full site editing (FSE)%2$s under "Admin Features" instead.', 'cleanup'), '<strong>', '</strong>'),
				'hook_type' => 'action',
				'hook' => 'admin_bar_menu',
				'priority' => 999,
				'pn' => 1,
				'group' => __('Block Editor', 'cleanup'),
			),

			'cleanup_remove_gutenberg_block_library' => array(
				'title' => __('Remove Gutenberg block library', 'cleanup'),
				'description' => __('Dequeue Gutenberg Block Library CSS Code Snippet', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 999,
				'pn' => 0,
				'group' => __('Block Editor', 'cleanup'),
			),

			'cleanup_remove_head_tags' => array(
				'title' => __('Remove head tags', 'cleanup'),
				'description' => sprintf(__('Removes the selected %1$s tags from the %2$s on all front-end pages.', 'cleanup'), '<code>&lt;link&gt;</code>', '<code>&lt;head&gt;</code>'),
				'options' => array(
					'rsd_link' => __('EditURI/RSD', 'cleanup'),
					'oembed_linktypes' => __('oEmbed Discovery Links', 'cleanup'),
					'resource_hints' => __('Resource Hints', 'cleanup'),
					'rest_output_link_wp_head' => __('REST API', 'cleanup'),
					'feed_links' => __('RSS Feeds', 'cleanup'),
					'wlwmanifest_link' => __('WLW Manifest', 'cleanup'),
					'wp_generator' => __('WP Generator', 'cleanup'),
					'wp_shortlink_wp_head' => __('WP Shortlink', 'cleanup'),
				),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Front End', 'cleanup'),
			),
			
			
			'cleanup_hide_wp_version' => array( 
				'title' => __('Hide WP Version', 'cleanup'),
				'description' => __('Removes WordPress version meta tag.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'after_setup_theme',
				'priority' => 10,
				'pn' => 0, 
				'group' => __('Front End', 'cleanup'),
			),

			'cleanup_remove_front_end_edit_links' => array(
				'title' => __('Remove front end Edit links', 'cleanup'),
				'description' => __('Removes Edit links that appear within the page layout of certain themes for logged-in users. Does not affect Edit links in the admin bar.', 'cleanup'),
				'hook_type' => 'filter',
				'hook' => 'edit_post_link',
				'priority' => 10,
				'pn' => 0,
				'cb' => '__return_false',
				'group' => __('Front End', 'cleanup'),
			),

			'cleanup_remove_howdy' => array(
				'title' => __('Remove "Howdy"', 'cleanup'),
				'description' => __('Removes "Howdy" greeting text (or the corresponding text in other languages) next to username in admin bar.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'admin_bar_menu',
				'priority' => 10,
				'pn' => 1,
				'group' => __('Admin Bar', 'cleanup'),
			),

			'cleanup_remove_posts_from_admin' => array(
				'title' => __('Remove Posts from admin', 'cleanup'),
				'description' => sprintf(__('If you use WordPress as a general-purpose CMS without a blog component, this option will hide the Posts link in the main admin navigation. It does %1$snot%2$s deactivate the "Posts" post type itself, nor restrict any front-end content. If you are using an SEO plugin, you will need to adjust its settings to exclude Posts from your sitemap XML.', 'cleanup'), '<strong>', '</strong>'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Admin Features', 'cleanup'),
			),
			
			'cleanup_remove_widgets_block_editor' => array(
				'title' => __('Remove Widgets block editor', 'cleanup'),
				'description' => __('Restores the previous default functionality of the Widgets page.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'after_setup_theme',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Block Editor', 'cleanup'),
			),
			'cleanup_remove_dashicons'=> array(
				'title' => __('Remove Dashicons', 'cleanup'),
				'description' => __('Disables dashicons on the front end when not logged in.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'wp_enqueue_scripts',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Front End', 'cleanup'),
			),
			'cleanup_remove_wp_emoji' => array(
				'title' => __('Remove WP emoji', 'cleanup'),
				'description' => __('Removes built-in emoji-related WordPress JavaScript code that normally gets loaded on every page. Also removes emoji tools in the TinyMCE editor.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Front End', 'cleanup'),
			),

			'cleanup_xmlrpc_disabled' => array(
				'title' => __('Disable XML-RPC', 'cleanup'),
				'description' => sprintf(__('Most WordPress sites do not use XML-RPC, although some plugins (e.g. Jetpack) and mobile applications may require it. Per changes in WordPress 3.5, turning this option on will only disable XML-RPC requests that require authentication. Use the %1$sAlso kill any incoming XML-RPC request%2$s option below to cause all incoming XML-RPC requests to exit early. (Note: Because this is a plugin-based solution, XML-RPC requests still must partially load, to the point where this plugin is active, before it can kill the process. For better performance during a DDOS attack, you may wish to block calls to %3$s directly in your site&rsquo;s %4$s file.', 'cleanup'), '<strong>', '</strong>', '<code>xmlrpc.php</code>', '<code>.htaccess</code>'),
				'hook_type' => 'action',
				'hook' => 'plugins_loaded',
				'options' => array(
					'kill_requests' => __('Also kill any incoming XML-RPC request', 'cleanup'),
				),
				'priority' => 11,
				'pn' => 0,
				'group' => __('Security and Updates', 'cleanup'),
			),
			'cleanup_svg_support' => array( 
				'title' => __('SVG upload support', 'cleanup'),
				'description' => __('Allow upload and support SVG type file format.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0, 
				'group' => __('Security and Updates', 'cleanup'),
			),
			'cleanup_add_csp_headers' => array( 
				'title' => __('Modify the CSP Header', 'cleanup'),
				'description' => __('Add the nonce to the CSP header.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0, 
				'group' => __('Security and Updates', 'cleanup'),
			),
			'cleanup_add_httponly_to_cookies' => array( 
				'title' => __('Set HttpOnly and secure flag for Cookies ', 'cleanup'),
				'description' => __('Add HttpOnly and secure flag for Cookies.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'wp_loaded',
				'priority' => 9999,
				'pn' => 0, 
				'group' => __('Security and Updates', 'cleanup'),
			)
		);
		
		// Conditional options (we don't show these if they're already set in wp-config.php
		if (!defined('CORE_UPGRADE_SKIP_NEW_BUNDLED')) {
			$this->function_details['cleanup_core_upgrade_skip_new_bundled'] = array(
				'title' => __('Core upgrade skip new bundled', 'cleanup'),
				'description' => sprintf(__('Skips installing things like new themes that are bundled by default with WordPress core upgrades. This can also be handled manually by adding the %1$sCORE_UPGRADE_SKIP_NEW_BUNDLED%2$s constant in your %3$swp-config.php%4$s file.', 'cleanup'), '<code>', '</code>', '<code>', '</code>'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Security and Updates', 'cleanup'),
			);
		}
		
		if (!defined('DISALLOW_FILE_EDIT')) {
			$this->function_details['cleanup_disallow_file_edit'] = array(
				'title' => __('Disallow theme and plugin file editing', 'cleanup'),
				'description' => __('Removes the ability for site admins to edit theme and plugin files directly within WordPress.', 'cleanup'),
				'hook_type' => 'action',
				'hook' => 'init',
				'priority' => 10,
				'pn' => 0,
				'group' => __('Admin Features', 'cleanup'),
			);
		}
		
		// Utility details
		$this->utility_details = array(

			'cleanup_deactivate_and_delete_hello_dolly' => array(
				'title' => __('Deactivate and delete Hello Dolly plugin', 'cleanup'),
				'description' => __('Deactivates and deletes the Hello Dolly plugin that is included by default in the default WordPress installation.', 'cleanup'),
			),

			'cleanup_delete_sample_content' => array(
				'title' => __('Delete sample content', 'cleanup'),
				'description' => __('Deletes the sample page, post, and comment that are included by default in a new WordPress installation.', 'cleanup'),
			),
			
			'cleanup_remove_default_tagline' => array(
				'title' => __('Remove default tagline', 'cleanup'),
				'description' => sprintf(__('Removes the default WordPress tagline ("%1$s"). You will probably want to add your own tagline in its place eventually, but it is easy to forget and it often appears in unexpected places.', 'cleanup'), __('Just another WordPress site', 'cleanup')),
			),

			'cleanup_set_permalink_structure_to_postname' => array(
				'title' => sprintf(__('Set permalink structure to %1$s', 'cleanup'), '<code style="font-weight: normal;">/%postname%/</code>'),
				'description' => __('Sets the permalink structure to the most commonly used option on modern websites, and flushes rewrite rules.', 'cleanup'),
			),

		);
		
		// Admin page
		add_action('admin_menu', array(&$this, 'admin_page'));

		// Enqueue admin scripts
		add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
		
		// Enqueue front-end scripts
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
		
		// Add our hooks based on plugin settings
		foreach ((array)$this->function_details as $name => $item) {
			$function = !empty($item['cb']) ? $item['cb'] : $name;
			if (!empty(get_option($name)) && function_exists($function)) {
				if ($item['hook_type'] == 'filter') {
					add_filter($item['hook'], $function, $item['priority'], $item['pn']);
				}
				else {
					add_action($item['hook'], $function, $item['priority'], $item['pn']);
				}
			}
		}
	
	}
	
	public function admin_page() {
		add_options_page(
			__('Cleanup', 'cleanup'),
			__('Cleanup', 'cleanup'),
			'manage_options',
			'cleanup',
			array(&$this, 'admin_page_callback'),
			34
		);
	}
	
	public function admin_enqueue_scripts() {
		wp_enqueue_script('cleanup-admin', plugin_dir_url(__FILE__) . 'assets/admin-script.js', array('jquery'));
		wp_enqueue_style('cleanup-admin-style', plugin_dir_url(__FILE__) . 'assets/admin-style.css', [], @Cleanup::VERSION);
		wp_enqueue_style('cleanup-admin-bar-style', plugin_dir_url(__FILE__) . 'assets/admin-bar.css', [], @Cleanup::VERSION);
	}

	public function admin_page_callback() {
	
		// Run utilities
		if (isset($_POST['cleanup-nonce-utilities']) && wp_verify_nonce($_POST['cleanup-nonce-utilities'], 'cleanup-nonce-utilities')) {
			
			$utilities_completed = array();
			
			foreach ((array)$this->utility_details as $name => $item) {
				if (isset($_POST[$name]) && $_POST[$name] == 'on') {
					if (function_exists($name)) {
						$status = $name();
						$utilities_completed[$name] = array(
							'title' => $item['title'],
							'status' => ($status !== false) ? '<span style="color: green; font-size: 1.25em;">&#9679;</span>' : '<span style="color: orange; font-size: 1.25em;">&#9679;</span>',
						);
					}
					else {
						$utilities_completed[$name] = array(
							'title' => $item['title'],
							'status' => '<span style="color: red; font-size: 1.25em;">&#9679;</span>',
						);
					}
				}
			}

			// Display admin notice
			echo '<div class="notice notice-success"><p>' . __('Utilities completed:', 'cleanup') . '</p>';
			foreach ($utilities_completed as $item) {
				echo '<p>' . wp_kses_post($item['status']) . ' &nbsp; ' . wp_kses_post($item['title']) . '</p>';
			}
			echo '</div>';
		}
	
		// Update settings
		if (isset($_POST['cleanup-nonce-settings']) && wp_verify_nonce($_POST['cleanup-nonce-settings'], 'cleanup-nonce-settings')) {

			foreach ((array)$_POST as $key => $value) {
				if (strpos($key, 'cleanup_') === 0) {
					if (strpos($key, '_options') !== false) {
						update_option($key, filter_var_array($value, FILTER_SANITIZE_NUMBER_INT));
					}
					else {
						delete_option($key); // Need to reset to erase options being deselected
						update_option($key, filter_var($value, FILTER_SANITIZE_NUMBER_INT));
					}
				}
			}

			// Display admin notice
			echo '<div class="notice notice-success"><p>' . __('Settings updated. You may need to refresh the page to see changes.', 'cleanup') . '</p></div>';
		}
		
		// Load page template
		include_once(plugin_dir_path(__FILE__) . 'templates/admin/cleanup-admin.php');

	}
	
	public function enqueue_scripts() {
		if (is_user_logged_in()) {
			wp_enqueue_style('cleanup-admin-bar-style', plugin_dir_url(__FILE__) . 'assets/admin-bar.css', [], @Cleanup::VERSION);
		}
	}

}
 