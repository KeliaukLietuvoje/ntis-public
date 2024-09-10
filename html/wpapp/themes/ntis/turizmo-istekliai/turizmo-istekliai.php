<?php
class NTIS_Tourism_Resources
{
    public function __construct()
    {
        add_filter('query_vars', function ($vars) {
            $vars[] = 'object';
            $vars[] = 'object_id';
            return $vars;
        });
        add_action('init', array($this, 'turizmo_istekliai_rewrite_rule'));
        add_action('wp_enqueue_scripts', array($this, 'turizmo_istekliai_css_js'));
    }
    public function turizmo_istekliai_rewrite_rule()
    {
        $current_lang = function_exists('pll_current_language') ? pll_current_language() : 'lt';
        $page_id = $this->get_id_by_slug($current_lang === 'lt' ? 'turizmo-istekliai' : 'tourism-resources');
        if ($page_id) {
            add_rewrite_rule(
                '^' . ($current_lang === 'lt' ? 'turizmo-istekliai' : 'en/tourism-resources') . '/([^/]*)/id:([0-9]+)/?$',
                'index.php?page_id=' . $page_id . '&object=$matches[1]&object_id=$matches[2]',
                'top'
            );
            flush_rewrite_rules();
        }
    }
    public static function fix_url($url)
    {
        if (!preg_match("/^https?:\/\//", $url)) {
            $url = "https://" . $url;
        }
        return $url;
    }
    public function turizmo_istekliai_css_js()
    {
        if (is_page_template('turizmo-istekliai/turizmo-istekliai-tpl.php')) {
            $maplibre_css_ver = $maplibre_js_ver = '4.3.2';
            wp_enqueue_style('maplibre-styles', '//unpkg.com/maplibre-gl@'.$maplibre_js_ver.'/dist/maplibre-gl.css', [], $maplibre_js_ver, 'all');
            wp_enqueue_script('maplibre-js', '//unpkg.com/maplibre-gl@'.$maplibre_css_ver.'/dist/maplibre-gl.js', ['jquery'], $maplibre_css_ver, true);
            wp_enqueue_style('turizmo-istekliai-styles', NTIS_THEME_URL . '/turizmo-istekliai/turizmo-istekliai.css', array(), '', 'all');
            if (!wp_script_is('swiper', 'enqueued')) {
                wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', ['jquery'], '11', true);
                wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11', 'all');
            }
            wp_enqueue_script('turizmo-istekliai', NTIS_THEME_URL . '/turizmo-istekliai/turizmo-istekliai.js', ['jquery'], '1.1.5', true);
            wp_localize_script(
                'turizmo-istekliai',
                'objVars',
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'map'=>[
                        'ico'=> NTIS_THEME_URL . '/inc/shortcodes/map/ikona.png',
                        'ico_width'=> 40,
                        'ico_height'=> 46,
                        'zoom'=> 10,
                    ]
                )
            );
        }
    }

    private function get_id_by_slug($page_slug)
    {
        $page = get_page_by_path($page_slug);
        if ($page) {
            return $page->ID;
        } else {
            return null;
        }
    }

    public static function loop_pagination($paged = '', $max_page = '', $query_args = [])
    {
        if (!$paged) {
            $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
        }

        if (!$max_page) {
            global $wp_query;
            $max_page = isset($wp_query->max_num_pages) ? $wp_query->max_num_pages : 1;
        }

        $html = paginate_links(array(
            'base'       => str_replace([ PHP_INT_MAX, '&#038;' ], [ '%#%', '&' ], esc_url(get_pagenum_link(PHP_INT_MAX))),
            'format'     => '',
            'current'    => max(1, $paged),
            'add_args'  => $query_args,
            'total'      => $max_page,
            'mid_size'   => 1,
            'end_size'	 => 1,
            'prev_text'  => __('«', 'am'),
            'next_text'  => __('»', 'am'),
        ));
        $html = "<div class='navigation tic-place__pagination'>" . $html . "</div>";

        return $html;
    }

    public static function fetch_endpoint($endpoint, $params = [])
    {
        $url = NTIS_API_URL. $endpoint . '?' . http_build_query($params);
        $response = wp_remote_get($url,
        [
            'timeout'     => 120,
            'httpversion' => CURL_HTTP_VERSION_1_1,
            'sslverify' => WP_DEBUG ? false : true,
            'headers' => [
            'Accept' => 'application/json'
            ]
        ]);
        if ((!is_wp_error($response)) && (200 === wp_remote_retrieve_response_code($response))) {
            $body = wp_remote_retrieve_body($response);
            return json_decode($body, true);
        }else{
            return [];
        }
    }

    public static function generate_tree_category($filter_categories, $items, $depth = 0) {
        $html = '<ul' . ($depth === 0 ? ' class="treeview"' : '') . '>';
        $index = 0;
    
        foreach ($items as $item) {
            $name = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
            $checkboxId = 'checkbox-' . $depth . '-' . $index;
            $checkboxName = 'filter[category][]';
            
            // Check if the current checkbox should be checked
            $isChecked = in_array($depth . '-' . $index.'|'.$name, $filter_categories ?? []) ? 'checked' : '';
            
            $html .= '<li class="nested-checkbox">';
            $html .= '<input type="checkbox" name="' . $checkboxName . '" id="' . $checkboxId . '" '
                   . $isChecked . ' value="' . $depth . '-' . $index . '|'.$name.'">';
            $html .= '<label for="' . $checkboxId . '">' . $name . '</label>';
            
            if (isset($item['children']) && is_array($item['children'])) {
                $html .= self::generate_tree_category($filter_categories, $item['children'], $depth + 1);
            }
            
            $html .= '</li>';
            $index++;
        }
        $html .= '</ul>';
        return $html;
    }
    
    
}

/*
<div class="nested-checkbox"><input type="checkbox" name="filter[category][]" id="filter-category-1" value="1" <?php checked(in_array(1, $params['category'] ?? []), true, true);?>><label for="filter-category-1"><?php _e('Turai', 'ntis');?></label></div>
                            <div class="nested-checkbox"><input type="checkbox" name="filter[category][]" id="filter-category-2" value="2" <?php checked(in_array(2, $params['category'] ?? []), true, true);?>><label for="filter-category-2"><?php _e('Verslo turizmas', 'ntis');?></label></div>
                            */

new NTIS_Tourism_Resources();
