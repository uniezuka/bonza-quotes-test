<?php

use PHPUnit\Framework\TestCase;

final class QuoteRepositoryTest extends TestCase {
    /** @var Bonza_Quote_Quote_Repository */
    private $repo;

    protected function setUp(): void {
        $this->repo = new Bonza_Quote_Quote_Repository();
    }

    public function test_insert_quote_sanitizes_and_sets_pending_status(): void {
        $result = $this->repo->insert_quote( array(
            'name' => '  <b>John Doe</b>  ',
            'email' => 'john@example.com ',
            'service_type' => '  Web <i>Design</i> ',
            'notes' => '<p>Hello <script>alert(1)</script>World</p>'
        ) );

        $this->assertIsInt( $result );
        $this->assertGreaterThan( 0, $result );

        // Verify row was saved via global $wpdb fake using repository API.
        $list = $this->repo->get_quotes();
        $this->assertSame( 1, $list['total'] );
        $row = $list['items'][0];

        $this->assertSame( 'John Doe', $row['name'] );
        $this->assertSame( 'john@example.com', $row['email'] );
        $this->assertSame( 'Web Design', $row['service_type'] );
        $this->assertSame( 'pending', $row['status'] );
        $this->assertStringNotContainsString( '<script>', $row['notes'] );
    }

    public function test_insert_quote_rejects_invalid_email(): void {
        $result = $this->repo->insert_quote( array(
            'name' => 'Jane',
            'email' => 'not-an-email',
            'service_type' => 'SEO',
            'notes' => 'Hi'
        ) );

        $this->assertInstanceOf( WP_Error::class, $result );
    }

    public function test_update_status_transitions_and_action(): void {
        $id = $this->repo->insert_quote( array(
            'name' => 'Mark',
            'email' => 'mark@example.com',
            'service_type' => 'Maintenance',
            'notes' => 'Please review'
        ) );
        $this->assertIsInt( $id );

        $res = $this->repo->update_status( $id, 'approved' );
        $this->assertTrue( $res === true );

        $list = $this->repo->get_quotes(array( 'status' => 'approved' ));
        $this->assertSame( 1, $list['total'] );
        $this->assertSame( 'approved', $list['items'][0]['status'] );

        // Invalid status is rejected.
        $err = $this->repo->update_status( $id, 'unknown' );
        $this->assertInstanceOf( WP_Error::class, $err );
    }
}


