<?php

function register_off_canvas_widget($widgets_manager)
{
    require_once(__DIR__ . '/off_canvas_widget.php');
    $widgets_manager->register(new \Elementor_Off_Canvas());

}
add_action('elementor/widgets/register', 'register_off_canvas_widget');

function register_off_canvas_widget_styles()
{
    wp_register_script('off_canvas_widget', NTIS_THEME_URL . '/inc/elementor/widgets/off_canvas/off_canvas_widget.js', [ 'jquery' ], '1.0.0', true);
    wp_register_style('off_canvas_widget', NTIS_THEME_URL . '/inc/elementor/widgets/off_canvas/off_canvas_widget.css');
}
add_action('wp_enqueue_scripts', 'register_off_canvas_widget_styles');
