<?php

defined('ABSPATH') || exit;

class NTIS_Map
{
    public function __construct()
    {
        add_shortcode('ntis_map', array($this, 'ntis_map_shortcode'));
    }
    public function ntis_map_shortcode($atts)
    {
        $current_lang = (function_exists('pll_current_language')) ? pll_current_language() : 'lt';
        $atts = shortcode_atts(
            array(
                'lang' => $current_lang,
                'coordinates' => '23.7486, 55.0904',
                'zoom' => '7',
                'pin' => NTIS_THEME_URL . '/inc/shortcodes/map/ikona.png',
                'pin_size' => '40,46',
                'map_height' => '800px',
                'add_layer' => 'false'
            ),
            $atts,
            'ntis_map'
        );

        $maplibre_css_ver = $maplibre_js_ver = '4.3.2';
        wp_enqueue_style('maplibre-styles', '//unpkg.com/maplibre-gl@'.$maplibre_js_ver.'/dist/maplibre-gl.css', [], $maplibre_js_ver, 'all');
        wp_enqueue_script('maplibre-js', '//unpkg.com/maplibre-gl@'.$maplibre_css_ver.'/dist/maplibre-gl.js', ['jquery'], $maplibre_css_ver, true);
        $ntis_map_js_ver  = date("ymd-His", filemtime(NTIS_THEME_DIR . '/inc/shortcodes/map/map.js'));
        $ntis_map_css_ver  = date("ymd-His", filemtime(NTIS_THEME_DIR . '/inc/shortcodes/map/map.css'));
        wp_enqueue_style('ntis-map-styles', NTIS_THEME_URL . '/inc/shortcodes/map/map.css', [], $ntis_map_css_ver, 'all');
        wp_enqueue_script('ntis-map-js', NTIS_THEME_URL . '/inc/shortcodes/map/map.js', ['jquery'], $ntis_map_js_ver, true);

        $lang = substr($atts['lang'], 0, 2);
        wp_localize_script('ntis-map-js', 'ntis_map_config', array(
            'api' => ['url' => NTIS_API_URL],
            'coordinates' => explode(',', $atts['coordinates']),
            'zoom' => (int)$atts['zoom'],
            'add_layer' => $atts['add_layer'],
            'pin' => [
                'url' => $atts['pin'],
                'size' => explode(',', $atts['pin_size'])
            ],
            'lang' => $lang,
            'more_url'=> home_url($lang === 'lt' ? 'turizmo-istekliai' : 'en/tourism-resources'),
            'i18n'=> [
                'more'=> __('Plačiau', 'ntis'),
                'object'=> __('Turizmo objektas', 'ntis'),
            ]
        ));

        return '<div class="ntis-map__wrapper"><div id="ntis-map" style="height:'.$atts['map_height'].'"></div></div>';
    }
}
new NTIS_Map();
