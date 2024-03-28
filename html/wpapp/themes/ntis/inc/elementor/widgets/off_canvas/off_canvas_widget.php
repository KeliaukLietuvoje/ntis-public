<?php
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Utils;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Elementor_Off_Canvas extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'tip-off-canvas';
    }

    public function get_title()
    {
        return esc_html__('Off canvas', 'tip');
    }

    public function get_icon()
    {
        return 'eicon-table-of-contents';
    }

    public function get_categories()
    {
        return [ 'tip-elements' ];
    }

    public function get_keywords()
    {
        return [ 'toc' ];
    }

    public function get_style_depends()
    {
        return [ 'off_canvas_widget' ];
    }

    public function get_script_depends()
    {
        return [ 'off_canvas_widget' ];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'additional_options',
            [
                'label' => esc_html__('Off canvas data', 'tip'),
            ]
        );

        $this->add_control(
            'menu_icon',
            [
                'label' => esc_html__('Menu icon', 'tip'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-circle',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->add_control(
            'close_icon',
            [
                'label' => __('Close icon', 'tip'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-star',
                    'library' => 'solid',
                ],
            ]
        );

        $this->add_control(
            'content',
            [
                'label' => __('Content', 'tip'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => __('Default content', 'tip'),
            ]
        );

        $this->end_controls_section(); // settings

    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        ?>
		<div class="offcanvas-content">
			<a class="offcanvas-close" style="display:none">
				<div class="my-icon-wrapper">
					<?php \Elementor\Icons_Manager::render_icon($settings['close_icon'], [ 'aria-hidden' => 'true' ]); ?>
				</div>
			</a>
			<?php echo do_shortcode($settings['content']);?>
		</div>
		<a class="offcanvas-open">
			<div class="my-icon-wrapper">
				<?php \Elementor\Icons_Manager::render_icon($settings['menu_icon'], [ 'aria-hidden' => 'true' ]); ?>
			</div>
		</a>
		<div class="offcanvas-overlay"></div>
	<?php
    }
    protected function content_template()
    {
    }
}
