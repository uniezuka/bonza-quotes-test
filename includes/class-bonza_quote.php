<?php

class Bonza_quote {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        if ( defined( 'BONZA_QUOTE_VERSION' ) ) {
            $this->version = BONZA_QUOTE_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'bonza_quote';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        $base_path = ( function_exists( 'plugin_dir_path' ) ) ? call_user_func( 'plugin_dir_path', dirname( __FILE__ ) ) : ( dirname( __DIR__ ) . DIRECTORY_SEPARATOR );
        require_once $base_path . 'includes/class-bonza_quote-loader.php';
        require_once $base_path . 'includes/class-bonza_quote-i18n.php';
        require_once $base_path . 'admin/class-bonza_quote-admin.php';
        require_once $base_path . 'public/class-bonza_quote-public.php';
        require_once $base_path . 'includes/class-bonza_quote-notifications.php';

        $this->loader = new Bonza_quote_Loader();
    }

    private function set_locale() {
        $plugin_i18n = new Bonza_quote_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    private function define_admin_hooks() {
        $plugin_admin = new Bonza_quote_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu' );
    }

    private function define_public_hooks() {
        $plugin_public = new Bonza_quote_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
        $this->loader->add_action( 'template_redirect', $plugin_public, 'maybe_handle_submission' );

        $notifications = new Bonza_Quote_Notifications();
        $this->loader->add_action( 'bonza_quote/submitted', $notifications, 'send_admin_email_on_submission', 10, 2 );
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
}
