<?php

if (!defined('ABSPATH')) {
    exit;
}

class NTIS_Elementor_Addons
{
    public function __construct()
    {
        $this->include_widgets();
        add_action('elementor/elements/categories_registered', array( $this, 'add_category' ));
        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_editor_icons'], 99);
        add_action('elementor/icons_manager/additional_tabs', [$this,'elementor_icons']);
        add_filter('elementor/fonts/additional_fonts', [$this,'register_custom_fonts']);
        add_action('elementor/controls/controls_registered', [$this,'modify_controls'], 10, 1);
    }
    public function modify_controls($controls_registry)
    {
        $fonts = $controls_registry->get_control('font')->get_settings('options');
        $new_fonts = array_merge([
            'StabilGrotesk' => 'system'
        ], $fonts);
        $controls_registry->get_control('font')->set_settings('options', $new_fonts);
    }
    public function register_custom_fonts($elementor_fonts)
    {
        $custom_fonts = array(
            'stabil-grotesk' => array(
                'label' => __('StabilGrotesk', 'text-domain'),
                'variants' => array( 'regular', 'bold' ),
                'category' => 'sans-serif',
                'family' => 'StabilGrotesk',
                'source' => 'local',
                'enqueue' => '',
                'fallback' => 'sans-serif',
            ),
        );

        $elementor_fonts = array_merge($elementor_fonts, $custom_fonts);

        return $elementor_fonts;
    }
    public function elementor_icons($tabs)
    {
        $tabs['ntis-custom'] = [
            'name'          => 'ntis-custom',
            'label'         => esc_html__('NTIS ikonos', 'ntis'),
            'prefix'        => 'ntis-',
            'displayPrefix' => 'ntis',
            'labelIcon'     => 'fa fa-font-awesome',
            'ver'           => '1.0.0',
            'fetchJson'     => NTIS_THEME_URL . '/inc/elementor/icons/json/ntis-custom.json',
            'native'        => true,
        ];

        return $tabs;
    }
    public function enqueue_editor_icons()
    {
        wp_enqueue_style('ntis-custom', NTIS_THEME_URL .'/inc/elementor/css/ntis-custom.css', array(), '1.0.0');
        if (ntis_elementor_is_edit_mode() || ntis_elementor_is_preview_page() || ntis_elementor_is_preview_mode()) {
            wp_enqueue_style('ntis-elementor-editor', NTIS_THEME_URL .'/inc/elementor/css/elementor-editor.css', array(), NTIS_VERSION);
        }
    }
    public function add_category($elements_manager)
    {
        $elements_manager->add_category(
            'ntis-elements',
            array(
                'title' => esc_html__('NTIS Elements', 'ntis'),
                'icon'  => 'fa fa-plug',
            )
        );
    }

    public function include_widgets()
    {
        $widgets = array(
            'off_canvas'
        );

        $widgets = apply_filters('ntis_customize_elements_array', $widgets);

        foreach ($widgets as $widget) {

            $render    = NTIS_THEME_DIR .'/inc/elementor/widgets/' . $widget . '/' . $widget . '.php';

            if(file_exists($render)) {
                require_once $render;
            }
        }
    }
}

new NTIS_Elementor_Addons();
