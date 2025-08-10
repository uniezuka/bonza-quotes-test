<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/uniezuka
 * @since      1.0.0
 *
 * @package    Bonza_quote
 * @subpackage Bonza_quote/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html__( 'Bonza Quotes', 'bonza_quote' ); ?></h1>

    <?php if ( ! empty( $_GET['message'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
        <div class="notice notice-error"><p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['message'] ) ) ); ?></p></div>
    <?php endif; ?>
    <?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
        <div class="notice notice-success"><p><?php echo esc_html( sprintf( __( 'Quote %s.', 'bonza_quote' ), sanitize_text_field( wp_unslash( $_GET['updated'] ) ) ) ); ?></p></div>
    <?php endif; ?>
    <?php if ( ! empty( $_GET['bulk'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
        <div class="notice notice-success"><p><?php echo esc_html( sprintf( __( 'Bulk %s complete for %d item(s).', 'bonza_quote' ), sanitize_text_field( wp_unslash( $_GET['bulk'] ) ), isset( $_GET['count'] ) ? (int) $_GET['count'] : 0 ) ); ?></p></div>
    <?php endif; ?>

    <form method="get">
        <input type="hidden" name="page" value="bonza_quotes" />
        <?php $list_table->search_box( __( 'Search Quotes', 'bonza_quote' ), 'bonza_quote_search' ); ?>
    </form>

    <form method="post" action="<?php echo esc_url( menu_page_url( 'bonza_quotes', false ) ); ?>">
        <input type="hidden" name="page" value="bonza_quotes" />
        <?php $list_table->display(); ?>
    </form>
</div>
