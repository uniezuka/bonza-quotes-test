<?php

if ( ! class_exists( 'Bonza_Quote_List_Table' ) ) {
    if ( ! class_exists( 'WP_List_Table' ) ) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }

    class Bonza_Quote_List_Table extends WP_List_Table {

        private $repository;

        public function __construct() {
            parent::__construct( array(
                'singular' => 'quote',
                'plural'   => 'quotes',
                'ajax'     => false,
            ) );

            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bonza_quote-quote-repository.php';
            $this->repository = new Bonza_Quote_Quote_Repository();
        }

        public function get_columns() {
            return array(
                'cb'           => '<input type="checkbox" />',
                'name'         => __( 'Name', 'bonza_quote' ),
                'email'        => __( 'Email', 'bonza_quote' ),
                'service_type' => __( 'Service Type', 'bonza_quote' ),
                'status'       => __( 'Status', 'bonza_quote' ),
                'date'         => __( 'Date', 'bonza_quote' ),
            );
        }

        protected function get_bulk_actions() {
            return array(
                'approve' => __( 'Approve', 'bonza_quote' ),
                'reject'  => __( 'Reject', 'bonza_quote' ),
            );
        }

        protected function column_cb( $item ) {
            return '<input type="checkbox" name="ids[]" value="' . (int) $item['id'] . '" />';
        }

        protected function column_name( $item ) {
            $name = esc_html( $item['name'] );

            $approve_url = wp_nonce_url( add_query_arg( array(
                'page'   => 'bonza_quotes',
                'action' => 'approve',
                'id'     => (int) $item['id'],
            ), admin_url( 'admin.php' ) ), 'bonza_quote_action_' . (int) $item['id'] );
            $reject_url = wp_nonce_url( add_query_arg( array(
                'page'   => 'bonza_quotes',
                'action' => 'reject',
                'id'     => (int) $item['id'],
            ), admin_url( 'admin.php' ) ), 'bonza_quote_action_' . (int) $item['id'] );

            $actions = array(
                'bonza_approve' => '<a href="' . esc_url( $approve_url ) . '">' . esc_html__( 'Approve', 'bonza_quote' ) . '</a>',
                'bonza_reject'  => '<a href="' . esc_url( $reject_url ) . '">' . esc_html__( 'Reject', 'bonza_quote' ) . '</a>',
            );

            return $name . ' ' . $this->row_actions( $actions );
        }

        protected function column_email( $item ) {
            $email = isset( $item['email'] ) ? $item['email'] : '';
            return '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
        }

        protected function column_service_type( $item ) {
            return esc_html( $item['service_type'] );
        }

        protected function column_status( $item ) {
            return esc_html( ucfirst( $item['status'] ) );
        }

        protected function column_date( $item ) {
            $timestamp = strtotime( $item['created_at'] );
            return esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) );
        }

        public function prepare_items() {
            $per_page = 20;
            $current_page = $this->get_pagenum();
            $search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

            $results = $this->repository->get_quotes( array(
                'paged'    => $current_page,
                'per_page' => $per_page,
                'search'   => $search,
            ) );

            $columns  = $this->get_columns();
            $hidden   = array();
            $sortable = array();
            $this->_column_headers = array( $columns, $hidden, $sortable, 'name' );

            $this->items = isset( $results['items'] ) ? $results['items'] : array();

            $total_items = isset( $results['total'] ) ? (int) $results['total'] : 0;
            $this->set_pagination_args( array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => $per_page ? (int) ceil( $total_items / $per_page ) : 1,
            ) );
        }

        public function process_bulk_action() {
            $action = $this->current_action();
            if ( ! in_array( $action, array( 'approve', 'reject' ), true ) ) {
                return;
            }

            $nonce_action = 'bulk-' . $this->_args['plural'];
            $raw_nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
            if ( ! $raw_nonce || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $raw_nonce ) ), $nonce_action ) ) {
                $_GET['message'] = __( 'Security check failed. Please try again.', 'bonza_quote' );
                return;
            }

            $ids = isset( $_REQUEST['ids'] ) ? (array) $_REQUEST['ids'] : array();
            $ids = array_map( 'absint', $ids );
            $ids = array_filter( $ids );
            if ( empty( $ids ) ) {
                return;
            }

            $new_status = 'approve' === $action ? 'approved' : 'rejected';
            $updated = 0;
            foreach ( $ids as $id ) {
                $result = $this->repository->update_status( $id, $new_status );
                if ( ! is_wp_error( $result ) ) {
                    $updated++;
                }
            }

            $_GET['bulk']  = $new_status;
            $_GET['count'] = $updated;
            return;
        }
    }
}


