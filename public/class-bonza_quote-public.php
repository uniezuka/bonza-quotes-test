<?php

class Bonza_quote_Public {
    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bonza_quote-public.css', array(), $this->version, 'all' );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bonza_quote-public.js', array( 'jquery' ), $this->version, false );
    }

    public function register_shortcodes() {
        add_shortcode( 'bonza_quote_form', array( $this, 'render_quote_form' ) );
    }

    public function maybe_handle_submission() {
        if ( ! isset( $_POST['bonza_quote_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bonza_quote_nonce'] ) ), 'bonza_quote_submit' ) ) {
            return;
        }

        $data = array(
            'name'         => isset( $_POST['name'] ) ? wp_unslash( $_POST['name'] ) : '',
            'email'        => isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '',
            'service_type' => isset( $_POST['service_type'] ) ? wp_unslash( $_POST['service_type'] ) : '',
            'notes'        => isset( $_POST['notes'] ) ? wp_unslash( $_POST['notes'] ) : '',
        );

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bonza_quote-quote-repository.php';
        $repo = new Bonza_Quote_Quote_Repository();
        $result = $repo->insert_quote( $data );

        if ( is_wp_error( $result ) ) {
            wp_safe_redirect( add_query_arg( 'bonza_quote_error', rawurlencode( $result->get_error_message() ), wp_get_referer() ) );
            exit;
        }

        wp_safe_redirect( add_query_arg( 'bonza_quote_submitted', '1', wp_get_referer() ) );
        exit;
    }

    public function render_quote_form() {
        $error   = isset( $_GET['bonza_quote_error'] ) ? sanitize_text_field( wp_unslash( $_GET['bonza_quote_error'] ) ) : '';
        $success = isset( $_GET['bonza_quote_submitted'] );

        ob_start();
        $types = apply_filters( 'bonza_quote/service_types', array( 'General Inquiry', 'Web Design', 'SEO', 'Maintenance' ) );
        $data  = array( 'types' => $types, 'error' => $error, 'success' => $success );
        include plugin_dir_path( __FILE__ ) . 'partials/bonza_quote-form.php';
        return ob_get_clean();
    }

}
