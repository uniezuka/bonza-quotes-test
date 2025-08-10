<?php

class Bonza_quote_Admin {
    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bonza_quote-admin.css', array(), $this->version, 'all' );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bonza_quote-admin.js', array( 'jquery' ), $this->version, false );
    }

    public function register_menu() {
        $hook = add_menu_page(
            __( 'Bonza Quotes', 'bonza_quote' ),
            __( 'Bonza Quotes', 'bonza_quote' ),
            'manage_options',
            'bonza_quotes',
            array( $this, 'render_quotes_page' ),
            'dashicons-feedback',
            26
        );

        if ( $hook ) {
            add_action( 'load-' . $hook, array( $this, 'load_quotes_page' ) );
        }
    }

    public function handle_actions() {
        if ( ! is_admin() ) {
            return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( empty( $_GET['page'] ) || 'bonza_quotes' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! $action || ! $id ) {
            return;
        }
        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! wp_verify_nonce( $nonce, 'bonza_quote_action_' . $id ) ) {
            return;
        }

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bonza_quote-quote-repository.php';
        $repo = new Bonza_Quote_Quote_Repository();
        $new  = 'approve' === $action ? 'approved' : ( 'reject' === $action ? 'rejected' : '' );
        if ( ! $new ) {
            return;
        }
        $result = $repo->update_status( $id, $new );

        if ( is_wp_error( $result ) ) {
            $redirect = add_query_arg( 'message', rawurlencode( $result->get_error_message() ), menu_page_url( 'bonza_quotes', false ) );
        } else {
            $redirect = add_query_arg( 'updated', $new, menu_page_url( 'bonza_quotes', false ) );
        }
        wp_safe_redirect( $redirect );
        exit;
    }

    public function render_quotes_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        require_once plugin_dir_path( __FILE__ ) . 'class-bonza_quote-list-table.php';
        $list_table = new Bonza_Quote_List_Table();
        $list_table->prepare_items();

        include plugin_dir_path( __FILE__ ) . 'partials/bonza_quote-admin-display.php';
    }

    public function load_quotes_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( in_array( $action, array( 'approve', 'reject' ), true ) && $id ) {
            $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( $nonce && wp_verify_nonce( $nonce, 'bonza_quote_action_' . $id ) ) {
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bonza_quote-quote-repository.php';
                $repo = new Bonza_Quote_Quote_Repository();
                $new  = 'approve' === $action ? 'approved' : 'rejected';
                $result = $repo->update_status( $id, $new );
                if ( is_wp_error( $result ) ) {
                    $redirect = add_query_arg( 'message', rawurlencode( $result->get_error_message() ), menu_page_url( 'bonza_quotes', false ) );
                    if ( headers_sent() ) {
                        $_GET['message'] = $result->get_error_message();
                        return;
                    }
                } else {
                    $redirect = add_query_arg( 'updated', $new, menu_page_url( 'bonza_quotes', false ) );
                    if ( headers_sent() ) {
                        $_GET['updated'] = $new;
                        return;
                    }
                }
                wp_safe_redirect( $redirect );
                exit;
            }
        }

        require_once plugin_dir_path( __FILE__ ) . 'class-bonza_quote-list-table.php';
        $list_table = new Bonza_Quote_List_Table();
        $list_table->process_bulk_action();
    }
}
