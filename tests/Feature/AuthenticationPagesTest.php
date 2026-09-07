<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthenticationPagesTest extends TestCase
{
    public function test_the_root_page_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_the_login_page_is_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('MENRO')
            ->assertSee('Sign In');
    }

    public function test_guests_are_redirected_from_protected_pages_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
