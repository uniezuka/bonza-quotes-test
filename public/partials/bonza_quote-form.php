<div class="bonza-quote-form">
    <h2 class="bonza-form-title"><?php echo esc_html__( 'Request a Quote', 'bonza_quote' ); ?></h2>
    <p class="bonza-form-subtitle"><?php echo esc_html__( 'Tell us a bit about your project and we’ll get back to you ASAP.', 'bonza_quote' ); ?></p>

    <?php if ( ! empty( $data['success'] ) ) : ?>
        <div class="notice notice-success" role="status" aria-live="polite">
            <?php echo esc_html__( 'Thanks! Your quote request was submitted.', 'bonza_quote' ); ?>
        </div>
    <?php elseif ( ! empty( $data['error'] ) ) : ?>
        <div class="notice notice-error" role="alert" aria-live="assertive">
            <?php echo esc_html( $data['error'] ); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <p>
            <label for="bonza_name"><?php echo esc_html__( 'Name', 'bonza_quote' ); ?></label><br />
            <input type="text" id="bonza_name" name="name" autocomplete="name" placeholder="<?php echo esc_attr__( 'Your full name', 'bonza_quote' ); ?>" required />
        </p>
        <p>
            <label for="bonza_email"><?php echo esc_html__( 'Email', 'bonza_quote' ); ?></label><br />
            <input type="email" id="bonza_email" name="email" autocomplete="email" placeholder="you@example.com" required />
        </p>
        <p>
            <label for="bonza_service"><?php echo esc_html__( 'Service Type', 'bonza_quote' ); ?></label><br />
            <select id="bonza_service" name="service_type" aria-label="<?php echo esc_attr__( 'Select a service type', 'bonza_quote' ); ?>">
                <?php foreach ( (array) $data['types'] as $type ) : ?>
                    <option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $type ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="bonza_notes"><?php echo esc_html__( 'Notes', 'bonza_quote' ); ?></label><br />
            <textarea id="bonza_notes" name="notes" rows="5" placeholder="<?php echo esc_attr__( 'Share any details, goals, or timelines…', 'bonza_quote' ); ?>"></textarea>
        </p>
        <?php wp_nonce_field( 'bonza_quote_submit', 'bonza_quote_nonce' ); ?>
        <p>
            <button type="submit" class="bonza-btn-primary"><?php echo esc_html__( 'Submit Quote', 'bonza_quote' ); ?></button>
        </p>
    </form>
</div>


