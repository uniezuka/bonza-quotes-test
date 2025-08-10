<?php
// Stub file so that including ABSPATH . 'wp-admin/includes/upgrade.php' works in tests.
if ( ! function_exists( 'dbDelta' ) ) {
    function dbDelta( $sql ) {
        $GLOBALS['__dbdelta_sql__'] = $sql;
        return true;
    }
}


