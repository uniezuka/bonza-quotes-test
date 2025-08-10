<?php

if ( ! class_exists( 'Bonza_Quote_Notifications' ) ) {
    class Bonza_Quote_Notifications {
        private function t( $text ) {
            return function_exists( '__' ) ? call_user_func( '__', $text, 'bonza_quote' ) : $text;
        }

        private function admin_url_safe( $path ) {
            return function_exists( 'admin_url' ) ? call_user_func( 'admin_url', $path ) : '/wp-admin/' . ltrim( $path, '/' );
        }

        private function strip_all_tags_safe( $text ) {
            if ( function_exists( 'wp_strip_all_tags' ) ) {
                return call_user_func( 'wp_strip_all_tags', (string) $text );
            }
            return strip_tags( (string) $text );
        }
        public function send_admin_email_on_submission( $quote_id, $data ) {
            if ( ! function_exists( 'get_option' ) || ! function_exists( 'wp_mail' ) ) {
                return;
            }
            $admin_email = function_exists( 'get_option' ) ? call_user_func( 'get_option', 'admin_email' ) : '';
            $is_valid_admin = function_exists( 'is_email' ) ? call_user_func( 'is_email', $admin_email ) : (bool) $admin_email;
            if ( ! $admin_email || ! $is_valid_admin ) {
                return;
            }

            $name         = isset( $data['name'] ) ? $data['name'] : '';
            $email        = isset( $data['email'] ) ? $data['email'] : '';
            $service_type = isset( $data['service_type'] ) ? $data['service_type'] : '';
            $notes        = isset( $data['notes'] ) ? $data['notes'] : '';

            $subject = sprintf( $this->t( 'New Quote Submission: %s' ), $name );

            $admin_page_url = $this->admin_url_safe( 'admin.php?page=bonza_quotes' );
            $body_lines = array(
                $this->t( 'A new quote request was submitted:' ),
                '',
                sprintf( $this->t( 'Name: %s' ), $name ),
                sprintf( $this->t( 'Email: %s' ), $email ),
                sprintf( $this->t( 'Service Type: %s' ), $service_type ),
                $this->t( 'Notes:' ),
                $this->strip_all_tags_safe( (string) $notes ),
                '',
                sprintf( $this->t( 'Review in admin: %s' ), $admin_page_url ),
            );
            $body = implode( "\n", $body_lines );

            /** Filter the admin email subject for new submissions. */
            if ( function_exists( 'apply_filters' ) ) {
                $subject = call_user_func( 'apply_filters', 'bonza_quote/admin_email_subject', $subject, $quote_id, $data );
            }

            /** Filter the admin email body for new submissions. */
            if ( function_exists( 'apply_filters' ) ) {
                $body = call_user_func( 'apply_filters', 'bonza_quote/admin_email_body', $body, $quote_id, $data );
            }

            $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
            call_user_func( 'wp_mail', $admin_email, $subject, $body, $headers );
        }
    }
}


