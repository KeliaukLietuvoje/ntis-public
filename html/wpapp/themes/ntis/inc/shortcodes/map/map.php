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
                'add_layer' => 'false',
                'filter_enabled' => 1,
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
        wp_enqueue_script('wkx-js', NTIS_THEME_URL . '/inc/shortcodes/map/wkx/dist/wkx.min.js', ['jquery'], $ntis_map_js_ver, true);
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
            'filter_enabled' => $atts['filter_enabled'],
            'lang' => $lang,
            'more_url' => home_url($lang === 'lt' ? 'turizmo-istekliai' : 'en/tourism-resources'),
            'i18n' => [
                'more' => __('Plačiau', 'ntis'),
                'object' => __('Turizmo objektas', 'ntis'),
                'filter' => __('Filtruoti', 'ntis'),
            ]
        ));

        $filter = '';
        $filter_category = $filter_subcategory = [];
        if ($atts['filter_enabled']) {
            $categories = NTIS_Tourism_Resources::fetch_endpoint('/categories/enum');
            $filter.='
                <form id="ntis-map-filters" method="get">
                <div class="search-container"> 
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search search-icon"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    
    <input type="text" id="filter_title" class="search-field" placeholder="'.__('Ieškoti pagal pavadinimą', 'sr').'" value="">
  </div>
                ';
            if(!empty($categories)){
             $filter.='<div class="dropdown">
                        <div class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
                        <div id="filter_category" class="filter-dropdown-toogle">'.__('Kategorija', 'ntis').'</div>
                        <span class="selected-count"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down dropdown-icon"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                        <nav class="dropdown-menu" aria-labelledby="dropdownToggle" style="display:none;">
                            <div class="ntis-map__filter_input_wrapper"><input type="text" id="ntis-map__filter-input" placeholder="'.__('Ieškoti', 'ntis').'"> <button type="button" id="ntis-map__filter-clear-input"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" class="delete-ico"><path d="M10 5a2 2 0 0 0-1.344.519l-6.328 5.74a1 1 0 0 0 0 1.481l6.328 5.741A2 2 0 0 0 10 19h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2z"/><path d="m12 9 6 6"/><path d="m18 9-6 6"/></svg></button></div>
                            <div id="filter_category_form" method="get">
                                '.NTIS_Tourism_Resources::generate_tree_category($lang, $filter_category, $filter_subcategory, $categories).'
                            </div>
                        </nav>
                    </div>';
            }
                    $filter.='<div class="dropdown">
                        <div class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
                        <div id="filter_price" class="filter-dropdown-toogle">'.__('Kaina', 'ntis').'</div>
                        <span class="selected-count"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down dropdown-icon"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                        <nav class="dropdown-menu" aria-labelledby="dropdownToggle" style="display:none;">
                            <div id="filter_price_form" method="get">
                                <label class="filter-label"><input type="checkbox" name="filter_price[]" value="paid" class="filter-checkbox">'.__('Mokama','ntis').'</label>
                                <label class="filter-label"><input type="checkbox" name="filter_price[]" value="free" class="filter-checkbox">'.__('Nemokama','ntis').'</label>
                            </div>
                        </nav>
                    </div>
                ';

                $additionalInfos = NTIS_Tourism_Resources::fetch_endpoint('/additionalInfos/enum');
                if(!empty($additionalInfos)){
                    $filter.='<div class="dropdown">
                        <div class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
                        <div id="filter_additionalinfo" class="filter-dropdown-toogle">'.__('Papildoma informacija', 'ntis').'</div>
                        <span class="selected-count"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down dropdown-icon"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                        <nav class="dropdown-menu" aria-labelledby="dropdownToggle" style="display:none;">
                            <div id="filter_additionalinfo_form" method="get">
                            ';
                            foreach ($additionalInfos as $additionalInfo) {
                                $v = ($current_lang == 'lt') ? $additionalInfo['name'] : $additionalInfo['nameEn'];
                                $filter.='<label class="filter-label" for="filter-additional-'.$additionalInfo['id'].'"><input type="checkbox" name="filter_additional[]" value="'.$additionalInfo['id'].'" id="filter-additional-'.$additionalInfo['id'].'" class="filter-checkbox" >'.$v.'</label>';
                            }
                            $filter.='</div>
                        </nav>
                    </div>';
                }
                    $filter.='<button type="button" id="ntis-map__filter-clear-filters"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg> '.__('Valyti filtrus','ntis').'</button>
                </form>';
        }
        return $filter.'<div class="ntis-map__wrapper"><div id="ntis-map" style="height:'.$atts['map_height'].'"></div></div>';
    }
}
new NTIS_Map();
