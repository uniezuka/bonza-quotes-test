<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/uniezuka
 * @since      1.0.0
 *
 * @package    Bonza_quote
 * @subpackage Bonza_quote/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Bonza_quote
 * @subpackage Bonza_quote/public
 * @author     J Lorenzo <junie.lorenzo@gmail.com>
 */
class Bonza_quote_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bonza_quote-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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


		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bonza_quote-public.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Register the frontend shortcode.
     */
    public function register_shortcodes() {
        add_shortcode( 'bonza_quote_form', array( $this, 'render_quote_form' ) );
    }

    /**
     * Handle form submission early on template_redirect
     */
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

    /**
     * Render the quote form shortcode.
     *
     * @return string
     */
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
