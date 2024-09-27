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
        add_action('wp_ajax_get_tourism_resources', array($this, 'get_tourism_resources'));
        add_action('wp_ajax_nopriv_get_tourism_resources', array($this, 'get_tourism_resources'));
    }
    public function get_tourism_resources()
    {
        $current_lang = (function_exists('pll_current_language')) ? pll_current_language() : 'lt';
        $page_size = isset($_REQUEST['limit']) ? absint($_REQUEST['limit']) : 12;
        $view = (isset($_REQUEST['view']) && $_REQUEST['view'] == 'list') ? 'list' : 'grid';
        $paged = $_REQUEST['paged'] ? absint($_REQUEST['paged']) : 1;
        $rest_url = NTIS_API_URL . '/public/forms';

        $params = [
            'page' => $paged,
            'pageSize' => $page_size,
            'sort' => '-createdAt',
        ];

        // Process filters
        $filters = $_REQUEST['filter'] ?? [];
        $filter_title = $filters['title'] ?? '';
        $filter_price = $filters['price'] ?? [];
        $filter_category = $filters['category'] ?? [];
        $filter_additional = $filters['additional'] ?? [];

        // Title filter
        if (!empty($filter_title)) {
            $param_key = ($current_lang == 'lt') ? 'query[nameLt][$ilike]' : 'query[nameEn][$ilike]';
            $params[$param_key] = '%' . sanitize_text_field($filter_title) . '%';
        }

        // Price filter
        if (!empty($filter_price) && count($filter_price) == 1) {
            $params['query[isPaid]'] = in_array('true', $filter_price) ? 'true' : 'false';
        }

        // Category filter
        if (!empty($filter_category)) {
            $params['query[categories][id][$in]'] = $filter_category;
        }

        // Additional filter
        if (!empty($filter_additional)) {
            $params['query[additionalInfos][id][$in]'] = $filter_additional;
        }

        // Build REST API URL with query string
        $query_string = http_build_query($params);
        $rest_url .= '?' . $query_string;

        // Make API request
        $response = wp_remote_get($rest_url, [
            'timeout' => 120,
            'httpversion' => CURL_HTTP_VERSION_1_1,
            'sslverify' => !WP_DEBUG,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        // Handle API response
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $response_data = json_decode(wp_remote_retrieve_body($response), true);

            if (json_last_error() === JSON_ERROR_NONE && !empty($response_data['rows'])) {
                ob_start();
                echo '<ul class="tic-place__places list '.$view.'">';

                foreach ($response_data['rows'] as $item) {
                    $title = ($current_lang == 'lt') ? $item['nameLt'] : $item['nameEn'];
                    $photo = $item['photos'][0] ?? null;
                    $photoUrl = $photo['url'] ?? '';
                    $photoName = !empty($photo['name']) ? esc_attr($photo['name']) . ', ' : '';
                    $photoAuthor = $photo['author'] ?? '';


                    echo '<li class="list-item">';
                    echo '<a href="'.get_the_permalink().sanitize_title($title).'/id:'.$item['id'].'/">';
                    if (!empty($photoUrl)) {
                        echo '<img src="' . esc_url($photoUrl) . '" alt="' . $photoName . (!empty($photoAuthor) ? ' ©' . esc_attr($photoAuthor) : '') . '" />';
                    } else {
                        echo '<img src="' . esc_url(NTIS_THEME_URL . '/assets/images/placeholder.png') . '" alt="' . __('Trūksta paveikslėlio', 'ntis') . '" />';
                    }
                    echo '</a>';
                    echo '<div>';
                    echo '<div class="tic-place__detail">
                            <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 10h12" />
                                <path d="M4 14h9" />
                                <path d="M19 6a7.7 7.7 0 0 0-5.2-2A7.9 7.9 0 0 0 6 12c0 4.4 3.5 8 7.8 8 2 0 3.8-.8 5.2-2" />
                            </svg>
                            <div class="tic-place__detail__desc">' . ($item['isPaid'] ? __('Mokama', 'ntis') : __('Nemokama', 'ntis')) . '</div>
                        </div>';
                    echo '<a href="' . get_the_permalink() . sanitize_title($title) . '/id:' . $item['id'] . '/" class="item-title">' . esc_html($title) . '</a>';

                    if (!empty($item['categories'])) {
                        echo '<div class="tic-place__categories tags-wrapper">';
                        foreach ($item['categories'] as $category) {
                            echo '<span class="tic-place__category tag">' . (($current_lang == 'lt') ? esc_html($category['name']) : esc_html($category['nameEn'])) . '</span>';
                        }
                        if (!empty($item['subCategories'])) {
                            foreach ($item['subCategories'] as $subcategory) {
                                echo '<span class="tic-place__category tag">' . (($current_lang == 'lt') ? esc_html($subcategory['name']) : esc_html($subcategory['nameEn'])) . '</span>';
                            }
                        }
                        echo '<span class="tic-place__category more-button">'.__('...', 'ntis').'</span>';
                        echo '</div>';

                    }
                    echo '</div>';
                    echo '</li>';
                }

                echo '</ul>';

                $html = ob_get_clean();
                $html .= self::loop_pagination($paged, ceil($response_data['total'] / $page_size), $params);
                wp_send_json_success(['html' => $html, 'total' => $response_data['total']]);
            } else {
                wp_send_json_success(['html' => __('Pagal pateiktus filtro kriterijus paieška rezultatų negrąžino.', 'ntis')]);
            }
        } else {
            wp_send_json_error(['message' => __('Nepavyko gauti duomenų iš serverio.', 'ntis')]);
        }

        wp_die();
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
                    'map' => [
                        'ico' => NTIS_THEME_URL . '/inc/shortcodes/map/ikona.png',
                        'ico_width' => 40,
                        'ico_height' => 46,
                        'zoom' => 10,
                    ],
                    'i18n' => [
                        'show_filters' => __('Rodyti filtrus', 'ntis'),
                        'hide_filters' => __('Slėpti filtrus', 'ntis'),
                        'show_more' => __('+ Rodyti daugiau', 'ntis'),
                        'show_less' => __('- Rodyti mažiau', 'ntis'),
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

        $current_lang = function_exists('pll_current_language') ? pll_current_language() : 'lt';

        $page = get_page_by_path('turizmo-istekliai');
        $page_id = pll_get_post($page->ID, $current_lang);
        $page = get_page($page_id);

        $base_url = home_url($page->post_name);
        $base_url = trailingslashit($base_url) . 'page/%#%/';

        $html = paginate_links(array(
            'base'       => $base_url,
            'format'     => '',
            'current'    => max(1, $paged),
            'add_args'   => [], // Add the query args if any
            'total'      => $max_page,
            'mid_size'   => 1,
            'end_size'   => 1,
            'prev_text'  => __('«', 'ntis'),
            'next_text'  => __('»', 'ntis'),
        ));
        $html = "<div class='navigation tic-place__pagination'>" . $html . "</div>";

        return $html;
    }

    public static function fetch_endpoint($endpoint, $params = [])
    {
        $url = NTIS_API_URL. $endpoint . '?' . http_build_query($params);
        $response = wp_remote_get(
            $url,
            [
            'timeout'     => 120,
            'httpversion' => CURL_HTTP_VERSION_1_1,
            'sslverify' => WP_DEBUG ? false : true,
            'headers' => [
            'Accept' => 'application/json'
            ]
        ]
        );
        if ((!is_wp_error($response)) && (200 === wp_remote_retrieve_response_code($response))) {
            $body = wp_remote_retrieve_body($response);
            return json_decode($body, true);
        } else {
            return [];
        }
    }

    public static function generate_tree_category($current_lang, $filter_categories, $filter_subcategories, $items, $depth = 0)
    {
        global $iteration;
        $html = '<ul' . ($depth === 0 ? ' class="treeview"' : '') . '>';
        $index = 0;

        foreach ($items as $item) {
            $name = $current_lang === 'lt' ? $item['name'] : $item['nameEn'];
            $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

            $checkboxId = 'checkbox-' . $item['id'];

            // Check if the current checkbox should be checked
            $isChecked = $depth == 0 ? (in_array($item['id'], $filter_categories ?? []) ? 'checked' : '') : (in_array($item['id'], $filter_subcategories ?? []) ? 'checked' : '');

            //$isChild = $depth == 0 ? ' category' : ' subcategory';
            if ($iteration > 4) {
                $showed = true;
                $more_options = ' class="more-options"';
            } else {
                $more_options = '';
            }
            $html .= '<li'.$more_options.'><label for="' . $checkboxId . '" class="nested-checkbox"><input type="checkbox" name="filter[category][]" id="' . $checkboxId . '" '
                   . $isChecked . ' value="' . $item['id'].'"><span>'. $name . '</span></label>';

            if (isset($item['children']) && is_array($item['children'])) {
                $html .= self::generate_tree_category($current_lang, $filter_categories, $filter_subcategories, $item['children'], $depth + 1);
            }

            $html .= '</li>';

            $index++;
            $iteration++;
        }
        $html .= '</ul>';
        return $html;
    }


}
new NTIS_Tourism_Resources();
