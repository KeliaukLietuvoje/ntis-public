<?php

class NTIS_Elementor
{
    public function __construct()
    {
        add_action('plugins_loaded', array( &$this, 'init' ), 99);
    }
    public function init()
    {

        add_action('init', array(&$this,'clear_cache'));
        add_action('elementor/elements/categories_registered', array(&$this, 'add_category' ));
        add_action('elementor/editor/after_enqueue_styles', array(&$this, 'enqueue_scripts_styles'), 99);
        add_action('elementor/icons_manager/additional_tabs', array(&$this,'elementor_icons'));
        if (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.5.0', '<')) {
            add_action('elementor/widgets/widgets_registered', array( $this, 'register_widgets' ));
        } else {
            add_action('elementor/widgets/register', array( $this, 'register_widgets' ));
        }
        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts_styles'));
    }
    private function is_dir_empty($dir)
    {
        if (!is_readable($dir)) {
            return null;
        }
        return (count(glob("$dir/*")) === 0);
    }
    public function clear_cache()
    {

        if ($this->is_dir_empty(WP_CONTENT_DIR . '/uploads/elementor/css')) {
            if (! did_action('elementor/loaded')) {
                return;
            }
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
    }
    private function is_activated()
    {
        if(function_exists('elementor_load_plugin_textdomain')) {
            return true;
        } else {
            return false;
        }
    }
    private function is_edit_mode()
    {
        if(!$this->is_activated()) {
            return false;
        }

        return Elementor\Plugin::$instance->editor->is_edit_mode();
    }
    private function is_preview_mode()
    {
        if(!$this->is_activated()) {
            return false;
        }

        return Elementor\Plugin::$instance->preview->is_preview_mode();
    }
    private function is_preview_page()
    {
        return isset($_GET['preview_id']);
    }
    public function elementor_icons($tabs)
    {
        $tabs['ntisico-custom'] = [
            'name'          => 'ntisico-custom',
            'label'         => esc_html__('NTIS ikonos', 'ntis'),
            'prefix'        => 'ntisico-',
            'displayPrefix' => 'ntisico',
            'labelIcon'     => 'ntisico ntisico-globe',
            'ver'           => '1.0.0',
            'fetchJson'     => NTIS_URI . '/assets/js/ntis-custom.json',
            'native'        => true,
        ];

        return $tabs;
    }
    public function enqueue_scripts_styles()
    {
        $suffix = SCRIPT_DEBUG ? '' : '.min';
        if(SCRIPT_DEBUG) {
            ntis('minify')->css(NTIS_DIR .'/assets/css/elementor-editor.css', NTIS_DIR .'/assets/css/elementor-editor.min.css');
            ntis('minify')->css(NTIS_DIR .'/assets/css/ntis-custom.css', NTIS_DIR .'/assets/css/ntis-custom.min.css');
            ntis('minify')->js(NTIS_DIR .'widgets/ntis_off_canvas/assets/js/off_canvas_widget.js', NTIS_DIR .'widgets/ntis_off_canvas/assets/js/off_canvas_widget.min.js');
            ntis('minify')->css(NTIS_DIR .'widgets/ntis_off_canvas/assets/css/off_canvas_widget.css', NTIS_DIR .'widgets/ntis_off_canvas/assets/css/off_canvas_widget.min.css');
        }

        wp_enqueue_style('ntis-custom', NTIS_URI .'/assets/css/ntis-custom' . $suffix . '.css', array(), NTIS_VERSION);
        if ($this->is_edit_mode() || $this->is_preview_page() || $this->is_preview_mode()) {
            wp_enqueue_style('ntis-elementor-editor', NTIS_URI .'/assets/css/elementor-editor' . $suffix . '.css', array(), NTIS_VERSION);
        }
        wp_register_script('ntis_off_canvas', NTIS_URI . 'widgets/ntis_off_canvas/assets/js/off_canvas_widget' . $suffix . '.js', [ 'jquery' ], '1.0.0', true);
        wp_register_style('ntis_off_canvas', NTIS_URI . 'widgets/ntis_off_canvas/assets/css/off_canvas_widget' . $suffix . '.css');
    }
    public function add_category($elements_manager)
    {
        $elements_manager->add_category(
            'ntis-elements',
            array(
                'title' => esc_html__('NTIS Elementai', 'ntis'),
                'icon'  => 'ntis ntis-globe',
            )
        );
    }
    public function register_widgets($widgets_manager)
    {
        $widgets_dir = NTIS_DIR . 'widgets/';

        if (!is_dir($widgets_dir)) {
            return;
        }
        $items = scandir($widgets_dir);

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (is_dir($widgets_dir . $item)) {
                $widget_file = $widgets_dir . $item . '/' . $item . '.php';
                require $widget_file;

                $class = str_replace('_', ' ', $item);
                $class = ucwords($class);
                $class = str_replace(' ', '_', $class);

                if (class_exists($class)) {
                    if (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.5.0', '<')) {
                        $widgets_manager->register_widget_type(new $class());
                    } else {
                        $widgets_manager->register(new $class());
                    }
                }
            }
        }
    }

}
