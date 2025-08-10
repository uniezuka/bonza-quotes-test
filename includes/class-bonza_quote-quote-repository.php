<?php

if ( ! class_exists( 'Bonza_Quote_Quote_Repository' ) ) {
    class Bonza_Quote_Quote_Repository {
        public static function get_table_name() {
            global $wpdb;
            return $wpdb->prefix . 'bonza_quotes';
        }

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

            /** Action fired after a quote is submitted. */
            do_action( 'bonza_quote/submitted', $quote_id, array(
                'name'         => $name,
                'email'        => $email,
                'service_type' => $service_type,
                'notes'        => $notes,
                'status'       => 'pending',
            ) );

            return $quote_id;
        }

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

            $items = $wpdb->get_results( $wpdb->prepare( $sql_items, array_merge( $params, array( $per_page, $offset ) ) ), ARRAY_A );

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

            /** Action fired when a quote status changes. */
            do_action( 'bonza_quote/status_changed', $id, (string) $current, $new_status );

            return true;
        }
    }
}


