<?php

defined('ABSPATH') || exit;

class NTIS_Map
{
    public function __construct()
    {
        add_shortcode('tip_map', array($this, 'tip_map_shortcode'));
    }
    public function tip_map_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'coordinates' => '23.7486, 55.0904',
                'zoom' => '7',
                'pin' => NTIS_THEME_URL . '/inc/shortcodes/map/pin3.svg',
                'pin_size' => '26,38',
                'map_height' => '800px',
                'add_layer' => 'false'
            ),
            $atts,
            'tip_map'
        );

        $maplibre_css_ver = $maplibre_js_ver = '4.3.2';
        wp_enqueue_style('maplibre-styles', '//unpkg.com/maplibre-gl@'.$maplibre_js_ver.'/dist/maplibre-gl.css', [], $maplibre_js_ver, 'all');
        wp_enqueue_script('maplibre-js', '//unpkg.com/maplibre-gl@'.$maplibre_css_ver.'/dist/maplibre-gl.js', ['jquery'], $maplibre_css_ver, true);
        $tip_map_js_ver  = date("ymd-His", filemtime(NTIS_THEME_DIR . '/inc/shortcodes/map/map.js'));
        $tip_map_css_ver  = date("ymd-His", filemtime(NTIS_THEME_DIR . '/inc/shortcodes/map/map.css'));
        wp_enqueue_style('ntis-map-styles', NTIS_THEME_URL . '/inc/shortcodes/map/map.css', [], $tip_map_css_ver, 'all');
        wp_enqueue_script('ntis-map-js', NTIS_THEME_URL . '/inc/shortcodes/map/map.js', ['jquery'], $tip_map_js_ver, true);


        wp_localize_script('ntis-map-js', 'tip_map_config', array(
            'api' => ['url' => NTIS_API_URL],
            'coordinates' => explode(',', $atts['coordinates']),
            'zoom' => (int)$atts['zoom'],
            'pin' => [
                'url' => $atts['pin'],
                'size' => explode(',', $atts['pin_size'])
            ],
            'add_layer' => $atts['add_layer']
        ));

        return '<div class="ntis-map__wrapper"><div id="ntis-map" style="height:'.$atts['map_height'].'"></div></div>';
    }
}
new NTIS_Map();
