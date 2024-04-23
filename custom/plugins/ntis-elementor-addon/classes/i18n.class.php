<?php 
class NTIS_i18n{
    public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ntis',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}
}