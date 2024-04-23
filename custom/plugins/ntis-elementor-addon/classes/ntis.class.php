<?php 
class NTIS{
    private $_classes = array();
    public function __construct()
    {
        register_activation_hook(NTIS_FILE, [$this,'activate']);
        register_deactivation_hook(NTIS_FILE, [$this,'deactivate']);

        require_once NTIS_DIR . 'classes/i18n.class.php';
        require_once NTIS_DIR . 'classes/minify.class.php';
        require_once NTIS_DIR . 'classes/elementor.class.php';
        require_once NTIS_DIR . 'classes/empty.class.php';

        $this->_classes = apply_filters(
            'ntis_classes',
            array(
                'i18n'          => new NTIS_i18n(),
                'minify'          => new NTIS_Minify(),
                'elementor'     => new NTIS_Elementor(),
                'empty'         => new NTIS_Empty()
            )
        );
    }
    public function __call($class, $args)
    {

        if (! isset($this->_classes[ $class ])) {
            if (WP_DEBUG) {
            } else {
                $class = 'empty';
            }
        }

        return $this->_classes[ $class ];
    }

    public function activate(){
        return true;
    }
    public function deactivate(){
        return true;
    }
    public function init()
    {

        ntis('i18n')->load_plugin_textdomain();
        do_action('ntis', $this);
    }
}