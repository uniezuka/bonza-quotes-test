<?php
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Optional cleanup: remove custom table
global $wpdb;
$table = $wpdb->prefix . 'bonza_quotes';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
