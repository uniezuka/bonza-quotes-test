<?php

class Bonza_quote_i18n {
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'bonza_quote',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }
}
