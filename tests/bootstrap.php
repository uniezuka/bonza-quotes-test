<?php
// Minimal bootstrap for repository tests without full WordPress.

// Define ABSPATH for includes that expect it. Point to tests/ so we can stub wp-admin includes.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . DIRECTORY_SEPARATOR );
}

// Small shims for core WP functions used in the classes.
if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = null ) { return $text; }
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        $str = (string) $str;
        $str = strip_tags( $str );
        $str = preg_replace( '/[\r\n\t]+/', ' ', $str );
        return trim( $str );
    }
}
if ( ! function_exists( 'sanitize_email' ) ) {
    function sanitize_email( $email ) {
        $email = trim( (string) $email );
        return filter_var( $email, FILTER_SANITIZE_EMAIL );
    }
}
if ( ! function_exists( 'is_email' ) ) {
    function is_email( $email ) {
        return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL );
    }
}

// Define common WordPress return-type constants used by $wpdb when absent.
if ( ! defined( 'ARRAY_A' ) ) { define( 'ARRAY_A', 'ARRAY_A' ); }
if ( ! defined( 'ARRAY_N' ) ) { define( 'ARRAY_N', 'ARRAY_N' ); }
if ( ! defined( 'OBJECT' ) )  { define( 'OBJECT', 'OBJECT' ); }
if ( ! defined( 'OBJECT_K' ) ) { define( 'OBJECT_K', 'OBJECT_K' ); }

// Provide absint helper if WP is not loaded.
if ( ! function_exists( 'absint' ) ) {
    function absint( $maybeint ) {
        return abs( (int) $maybeint );
    }
}
if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post( $content ) {
        // Allow a minimal set of tags for testing.
        return strip_tags( (string) $content, '<a><br><em><strong><p>' );
    }
}
if ( ! function_exists( 'current_time' ) ) {
    function current_time( $type, $gmt = 0 ) {
        if ( 'mysql' === $type ) {
            if ( $gmt ) {
                return gmdate( 'Y-m-d H:i:s' );
            }
            return date( 'Y-m-d H:i:s' );
        }
        return time();
    }
}
if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        public $errors = array();
        public function __construct( $code = '', $message = '' ) {
            if ( $code ) {
                $this->errors[ $code ] = array( $message );
            }
        }
    }
}
if ( ! function_exists( 'do_action' ) ) {
    function do_action( $hook_name, ...$args ) {
        // No-op for unit tests.
    }
}

// Minimal $wpdb mock used by repository.
class FakeWpdb {
    public $prefix = 'wp_';
    private $rows = array();
    public $insert_id = 0;

    public function get_charset_collate() { return 'CHARSET=utf8'; }
    public function esc_like( $text ) { return addslashes( $text ); }

    public function insert( $table, $data, $format ) {
        if ( $table !== $this->prefix . 'bonza_quotes' ) {
            return false;
        }
        $this->insert_id += 1;
        $row = $data;
        $row['id'] = $this->insert_id;
        $this->rows[ $row['id'] ] = $row;
        return 1;
    }

    public function update( $table, $data, $where, $format, $where_format ) {
        $id = (int) $where['id'];
        if ( ! isset( $this->rows[ $id ] ) ) {
            return false;
        }
        foreach ( $data as $k => $v ) {
            $this->rows[ $id ][ $k ] = $v;
        }
        return 1;
    }

    public function get_var( $query ) {
        // Handle COUNT(*) with optional WHERE status = '...'
        if ( preg_match( '/SELECT COUNT\(\*\) FROM\s+\w+\s*(WHERE\s+(.+))?/i', $query, $m ) ) {
            $filtered = $this->rows;
            if ( ! empty( $m[1] ) ) {
                if ( preg_match( '/status\s*=\s*\'([^\']*)\'/i', $query, $sm ) ) {
                    $status = $sm[1];
                    $filtered = array_filter( $filtered, function( $r ) use ( $status ) { return isset( $r['status'] ) && $r['status'] === $status; } );
                }
            }
            return count( $filtered );
        }
        if ( preg_match( '/SELECT\s+status\s+FROM\s+(\w+)\s+WHERE\s+id\s*=\s*(\d+)/i', $query, $m ) ) {
            $id = (int) $m[2];
            return isset( $this->rows[ $id ] ) ? $this->rows[ $id ]['status'] : null;
        }
        return null;
    }

    public function get_results( $query, $output ) {
        // Apply rudimentary WHERE status filter if present.
        $rows = array_values( $this->rows );
        if ( preg_match( '/WHERE\s+(.+)\s+ORDER BY/i', $query, $wm ) ) {
            $where = $wm[1];
            if ( preg_match( '/status\s*=\s*\'([^\']*)\'/i', $where, $sm ) ) {
                $status = $sm[1];
                $rows = array_values( array_filter( $rows, function( $r ) use ( $status ) { return isset( $r['status'] ) && $r['status'] === $status; } ) );
            }
        }
        usort( $rows, function ( $a, $b ) { return strcmp( $b['created_at'], $a['created_at'] ); } );
        if ( preg_match( '/LIMIT\s+(\d+)\s+OFFSET\s+(\d+)/i', $query, $m ) ) {
            $limit = (int) $m[1];
            $offset = (int) $m[2];
            $rows = array_slice( $rows, $offset, $limit );
        }
        return $rows;
    }

    public function prepare( $query, $args ) {
        // Replace placeholders sequentially, respecting the placeholder type.
        if ( ! is_array( $args ) ) {
            $args = array_slice( func_get_args(), 1 );
        }
        foreach ( $args as $arg ) {
            // Find next placeholder occurrence
            $posS = strpos( $query, '%s' );
            $posD = strpos( $query, '%d' );
            if ( $posS === false && $posD === false ) {
                break;
            }
            if ( $posS !== false && ( $posD === false || $posS < $posD ) ) {
                // Next placeholder is %s
                $replacement = "'" . addslashes( (string) $arg ) . "'";
                $query = substr_replace( $query, $replacement, $posS, 2 );
            } else {
                // Next placeholder is %d
                $replacement = (string) (int) $arg;
                $query = substr_replace( $query, $replacement, $posD, 2 );
            }
        }
        return $query;
    }
}

// Expose the fake as global $wpdb for repository usage.
global $wpdb;
$wpdb = new FakeWpdb();

// Load repository class and any direct dependencies it uses.
require_once __DIR__ . '/../includes/class-bonza_quote-quote-repository.php';

// Load activator class for integration-style test; its include of upgrade.php will resolve to tests/wp-admin/... stub.
require_once __DIR__ . '/../includes/class-bonza_quote-activator.php';


