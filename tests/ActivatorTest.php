<?php

use PHPUnit\Framework\TestCase;

// Provide a stub for dbDelta included from wp-admin/includes/upgrade.php.
if ( ! function_exists( 'dbDelta' ) ) {
    function dbDelta( $sql ) {
        // For our purposes, we simply assert the SQL contains the expected table name.
        $GLOBALS['__dbdelta_sql__'] = $sql;
        return true;
    }
}

final class ActivatorTest extends TestCase {
    public function test_activate_generates_expected_create_table_sql(): void {
        // Ensure global $wpdb is present from bootstrap
        global $wpdb;
        $this->assertNotNull( $wpdb );

        // Call activator
        Bonza_quote_Activator::activate();

        $this->assertArrayHasKey( '__dbdelta_sql__', $GLOBALS );
        $sql = $GLOBALS['__dbdelta_sql__'];

        $this->assertStringContainsString( $wpdb->prefix . 'bonza_quotes', $sql );
        $this->assertStringContainsString( 'id bigint(20) unsigned NOT NULL AUTO_INCREMENT', $sql );
        $this->assertStringContainsString( 'status varchar(20) NOT NULL DEFAULT \'pending\'', $sql );
    }
}


