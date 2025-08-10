<?php
namespace PHPUnit\Framework;

// Stub for static analysis/linting in this repo. At runtime, PHPUnit's own
// TestCase will be used; this file is not included by tests.
abstract class TestCase {
    public function assertIsInt( $actual, string $message = '' ) {}
    public function assertGreaterThan( $expected, $actual, string $message = '' ) {}
    public function assertSame( $expected, $actual, string $message = '' ) {}
    public function assertStringNotContainsString( string $needle, string $haystack, string $message = '' ) {}
    public function assertInstanceOf( $expected, $actual, string $message = '' ) {}
    public function assertTrue( $condition, string $message = '' ) {}
    public function assertNotNull( $actual, string $message = '' ) {}
    public function assertArrayHasKey( $key, $array, string $message = '' ) {}
    public function assertStringContainsString( string $needle, string $haystack, string $message = '' ) {}
}


