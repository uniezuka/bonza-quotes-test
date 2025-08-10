<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/uniezuka
 * @since      1.0.0
 *
 * @package    Bonza_quote
 * @subpackage Bonza_quote/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Bonza_quote
 * @subpackage Bonza_quote/includes
 * @author     J Lorenzo <junie.lorenzo@gmail.com>
 */
class Bonza_quote_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        global $wpdb;

        $table_name      = $wpdb->prefix . 'bonza_quotes';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(191) NOT NULL,
            email varchar(191) NOT NULL,
            service_type varchar(100) NOT NULL,
            notes text NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
	}

}
