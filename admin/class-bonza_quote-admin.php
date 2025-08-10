<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/uniezuka
 * @since      1.0.0
 *
 * @package    Bonza_quote
 * @subpackage Bonza_quote/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bonza_quote
 * @subpackage Bonza_quote/admin
 * @author     J Lorenzo <junie.lorenzo@gmail.com>
 */
class Bonza_quote_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bonza_quote_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bonza_quote_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bonza_quote-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bonza_quote_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bonza_quote_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bonza_quote-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Register admin menu for Bonza Quotes
     */
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

        // Process bulk actions early before any output is sent.
        if ( $hook ) {
            add_action( 'load-' . $hook, array( $this, 'load_quotes_page' ) );
        }
    }

    /**
     * Handle approve/reject actions
     */
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

    /**
     * Render admin page listing quotes
     */
    public function render_quotes_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        require_once plugin_dir_path( __FILE__ ) . 'class-bonza_quote-list-table.php';
        $list_table = new Bonza_Quote_List_Table();
        $list_table->prepare_items();

        include plugin_dir_path( __FILE__ ) . 'partials/bonza_quote-admin-display.php';
    }

    /**
     * Handle bulk actions for the quotes page on load hook (before any output).
     */
    public function load_quotes_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        // Handle single row actions (approve/reject) safely before output.
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

        // Handle bulk actions via WP_List_Table helper.
        require_once plugin_dir_path( __FILE__ ) . 'class-bonza_quote-list-table.php';
        $list_table = new Bonza_Quote_List_Table();
        $list_table->process_bulk_action();
    }
}
