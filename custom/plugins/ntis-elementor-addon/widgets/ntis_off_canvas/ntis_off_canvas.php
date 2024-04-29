<?php
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Utils;

if (! defined('ABSPATH')) {
    exit;
}

class Ntis_Off_Canvas extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'ntis_off_canvas';
    }

    public function get_title()
    {
        return esc_html__('NTIS - Off Canvas', 'ntis');
    }

    public function get_icon()
    {
        return 'eicon-table-of-contents';
    }

    public function get_categories()
    {
        return [ 'elements' ];
    }

    public function get_keywords()
    {
        return [ 'ntis','offcanvas' ];
    }

    public function get_style_depends()
    {
        return [ 'ntis_off_canvas' ];
    }

    public function get_script_depends()
    {
        return [ 'ntis_off_canvas' ];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'additional_options',
            [
                'label' => esc_html__('Off canvas data', 'ntis'),
            ]
        );

        $this->add_control(
            'menu_icon',
            [
                'label' => esc_html__('Menu icon', 'ntis'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'ntisico ntisico-menu',
                    'library' => 'ntisico-custom',
                ],
            ]
        );

        $this->add_control(
            'close_icon',
            [
                'label' => __('Close icon', 'ntis'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'ntisico ntisico-x',
                    'library' => 'ntisico-custom',
                ],
            ]
        );

        $this->add_control(
            'menu_icon_size',
            [
                'label' => esc_html__('Menu Icon Size', 'ntis'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'size' => 32,
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ntis-off-canvas-open .ntis-icon-wrapper i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'close_icon_size',
            [
                'label' => __('Close Icon Size', 'ntis'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'size' => 32,
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ntis-off-canvas-close .ntis-icon-wrapper i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content',
            [
                'label' => __('Content', 'ntis'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => __('Default content', 'ntis'),
            ]
        );

        $this->end_controls_section();

    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        ?>
		<div class="ntis-off-canvas-content">
			<a class="ntis-off-canvas-close" style="display:none">
				<div class="ntis-icon-wrapper">
					<?php \Elementor\Icons_Manager::render_icon($settings['close_icon'], [ 'aria-hidden' => 'true' ]); ?>
				</div>
			</a>
			<?php echo do_shortcode($settings['content']);?>
		</div>
		<a class="ntis-off-canvas-open">
			<div class="ntis-icon-wrapper">
				<?php \Elementor\Icons_Manager::render_icon($settings['menu_icon'], [ 'aria-hidden' => 'true' ]); ?>
			</div>
		</a>
		<div class="ntis-off-canvas-overlay"></div>
	<?php
    }
}
