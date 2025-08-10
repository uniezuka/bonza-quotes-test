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

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BONZA_QUOTE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bonza_quote-activator.php
 */
function activate_bonza_quote() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bonza_quote-activator.php';
	Bonza_quote_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bonza_quote-deactivator.php
 */
function deactivate_bonza_quote() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bonza_quote-deactivator.php';
	Bonza_quote_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bonza_quote' );
register_deactivation_hook( __FILE__, 'deactivate_bonza_quote' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bonza_quote.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bonza_quote() {

	$plugin = new Bonza_quote();
	$plugin->run();

}
run_bonza_quote();
