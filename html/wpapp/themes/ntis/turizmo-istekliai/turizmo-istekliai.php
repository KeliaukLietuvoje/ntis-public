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
        $page_id = $this->get_id_by_slug('turizmo-istekliai');
        add_rewrite_rule(
            '^turizmo-istekliai/([^/]*)/id:([0-9]+)/?$',
            'index.php?page_id='.$page_id.'&object=$matches[1]&object_id=$matches[2]',
            'top'
        );

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
            wp_enqueue_script('turizmo-istekliai', NTIS_THEME_URL . '/turizmo-istekliai/turizmo-istekliai.js', ['jquery'], '1.1.5', true);
            wp_localize_script(
                'turizmo-istekliai',
                'objVars',
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'form_action' => get_the_permalink(1414)
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
}

new NTIS_Tourism_Resources();
