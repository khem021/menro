<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public function expectedHeaders(): array
    {
        return [
            'clickjacking (CSP)' => ['Content-Security-Policy', "frame-ancestors 'none'"],
            'clickjacking (XFO)' => ['X-Frame-Options', 'DENY'],
            'mime sniffing'      => ['X-Content-Type-Options', 'nosniff'],
            'referrer'           => ['Referrer-Policy', 'strict-origin-when-cross-origin'],
            'cross-domain'       => ['X-Permitted-Cross-Domain-Policies', 'none'],
        ];
    }

    /** @dataProvider expectedHeaders */
    public function test_the_login_page_carries_the_hardening_headers(string $header, string $value): void
    {
        $this->get('/login')->assertHeader($header, $value);
    }

    public function test_the_permissions_policy_disables_unused_device_apis(): void
    {
        $policy = $this->get('/login')->headers->get('Permissions-Policy');

        foreach (['camera', 'microphone', 'geolocation'] as $feature) {
            $this->assertStringContainsString("{$feature}=()", $policy);
        }
    }

    /** HSTS over plain HTTP would pin a developer's browser to https://menro.test. */
    public function test_hsts_is_withheld_over_plain_http(): void
    {
        $this->get('/login')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_sent_over_https(): void
    {
        $this->get('https://localhost/login')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    /** Livewire message requests bypass route middleware, so check one directly. */
    public function test_error_responses_carry_the_headers_too(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
