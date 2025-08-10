<?php

/**
 * Quote Repository - Data access for bonza quotes
 *
 * @package Bonza_quote
 * @subpackage Bonza_quote/includes
 */

if ( ! class_exists( 'Bonza_Quote_Quote_Repository' ) ) {
    class Bonza_Quote_Quote_Repository {

        /**
         * Get table name with prefix
         *
         * @return string
         */
        public static function get_table_name() {
            global $wpdb;
            return $wpdb->prefix . 'bonza_quotes';
        }

        /**
         * Insert a new quote
         *
         * @param array $data { name, email, service_type, notes }
         * @return int|WP_Error Inserted row ID or WP_Error
         */
        public function insert_quote( array $data ) {
            global $wpdb;

            $name         = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
            $email        = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
            $service_type = isset( $data['service_type'] ) ? sanitize_text_field( $data['service_type'] ) : '';
            $notes        = isset( $data['notes'] ) ? wp_kses_post( $data['notes'] ) : '';

            if ( empty( $name ) || empty( $email ) || ! is_email( $email ) ) {
                return new WP_Error( 'invalid_data', __( 'Invalid name or email.', 'bonza_quote' ) );
            }

            $table = self::get_table_name();
            $now   = current_time( 'mysql', 1 );

            $inserted = $wpdb->insert(
                $table,
                array(
                    'name'         => $name,
                    'email'        => $email,
                    'service_type' => $service_type,
                    'notes'        => $notes,
                    'status'       => 'pending',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ),
                array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );

            if ( false === $inserted ) {
                return new WP_Error( 'db_insert_error', __( 'Failed to save quote.', 'bonza_quote' ) );
            }

            $quote_id = (int) $wpdb->insert_id;

            /**
             * Action fired after a quote is submitted.
             *
             * @param int   $quote_id
             * @param array $data
             */
            do_action( 'bonza_quote/submitted', $quote_id, array(
                'name'         => $name,
                'email'        => $email,
                'service_type' => $service_type,
                'notes'        => $notes,
                'status'       => 'pending',
            ) );

            return $quote_id;
        }

        /**
         * Get quotes with optional search and pagination
         *
         * @param array $args { search, status, paged, per_page }
         * @return array { items: array, total: int }
         */
        public function get_quotes( array $args = array() ) {
            global $wpdb;
            $table = self::get_table_name();

            $search   = isset( $args['search'] ) ? sanitize_text_field( $args['search'] ) : '';
            $status   = isset( $args['status'] ) ? sanitize_text_field( $args['status'] ) : '';
            $paged    = max( 1, isset( $args['paged'] ) ? absint( $args['paged'] ) : 1 );
            $per_page = max( 1, isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 20 );
            $offset   = ( $paged - 1 ) * $per_page;

            $where   = array();
            $params  = array();

            if ( $search ) {
                $like = '%' . $wpdb->esc_like( $search ) . '%';
                $where[] = '(name LIKE %s OR email LIKE %s OR service_type LIKE %s)';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }

            if ( $status ) {
                $where[] = 'status = %s';
                $params[] = $status;
            }

            $where_sql = $where ? ( 'WHERE ' . implode( ' AND ', $where ) ) : '';

            $sql_items = "SELECT * FROM {$table} {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
            $sql_count = "SELECT COUNT(*) FROM {$table} {$where_sql}";

            // Always prepare items (always has two %d placeholders)
            $items = $wpdb->get_results( $wpdb->prepare( $sql_items, array_merge( $params, array( $per_page, $offset ) ) ), ARRAY_A );

            // Prepare count only when there are where params; otherwise query directly
            if ( ! empty( $params ) ) {
                $total = (int) $wpdb->get_var( $wpdb->prepare( $sql_count, $params ) );
            } else {
                $total = (int) $wpdb->get_var( $sql_count );
            }

            return array(
                'items' => $items,
                'total' => $total,
            );
        }

        /**
         * Update quote status
         *
         * @param int    $id
         * @param string $new_status approved|rejected|pending
         * @return bool|WP_Error
         */
        public function update_status( $id, $new_status ) {
            global $wpdb;
            $id         = absint( $id );
            $new_status = sanitize_text_field( $new_status );
            $allowed    = array( 'pending', 'approved', 'rejected' );
            if ( ! in_array( $new_status, $allowed, true ) ) {
                return new WP_Error( 'invalid_status', __( 'Invalid status.', 'bonza_quote' ) );
            }

            $table = self::get_table_name();
            $now   = current_time( 'mysql', 1 );

            $current = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$table} WHERE id = %d", $id ) );
            if ( null === $current ) {
                return new WP_Error( 'not_found', __( 'Quote not found.', 'bonza_quote' ) );
            }

            $updated = $wpdb->update(
                $table,
                array( 'status' => $new_status, 'updated_at' => $now ),
                array( 'id' => $id ),
                array( '%s', '%s' ),
                array( '%d' )
            );

            if ( false === $updated ) {
                return new WP_Error( 'db_update_error', __( 'Failed to update status.', 'bonza_quote' ) );
            }

            /**
             * Action fired when a quote status changes.
             *
             * @param int    $quote_id
             * @param string $old_status
             * @param string $new_status
             */
            do_action( 'bonza_quote/status_changed', $id, (string) $current, $new_status );

            return true;
        }
    }
}


