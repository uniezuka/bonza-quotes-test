<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/uniezuka
 * @since             1.0.0
 * @package           Bonza_quote
 *
 * @wordpress-plugin
 * Plugin Name:       Bonza Quote Form
 * Plugin URI:        https://github.com/uniezuka
 * Description:       A custom WordPress plugin that implements a simplified version of our quote workflow. The goal is to simulate how we handle incoming service quote requests, approvals, and admin interaction.
 * Version:           1.0.0
 * Author:            J Lorenzo
 * Author URI:        https://github.com/uniezuka/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bonza_quote
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'BONZA_QUOTE_VERSION', '1.0.0' );

function activate_bonza_quote() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bonza_quote-activator.php';
	Bonza_quote_Activator::activate();
}

function deactivate_bonza_quote() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bonza_quote-deactivator.php';
	Bonza_quote_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bonza_quote' );
register_deactivation_hook( __FILE__, 'deactivate_bonza_quote' );

require plugin_dir_path( __FILE__ ) . 'includes/class-bonza_quote.php';

function run_bonza_quote() {
    $plugin = new Bonza_quote();
    $plugin->run();
}
run_bonza_quote();
