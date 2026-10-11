<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Phase 4 (UI/UX) checks that can be asserted from the rendered HTML: role-aware
 * navigation and buttons, error pages, and the accessibility hooks.
 */
class UiPassTest extends TestCase
{
    use DatabaseTransactions;

    private function as(string $username): void
    {
        $u = User::where('username', $username)->firstOrFail();
        $this->flushSession();
        $state = [
            'auth_user_id' => $u->user_id,
            'auth_role'    => $u->role->role_name,
            'auth_pw'      => passwordFingerprint($u->password_hash),
        ];
        session($state);
        $this->withSession($state);
    }

    public function test_the_sidebar_hides_the_admin_section_from_staff(): void
    {
        $this->as('menro');
        $html = $this->get('/dashboard')->assertOk()->getContent();

        // User management and the audit trail are the only sections
        // the two roles still differ on.
        foreach (['/users', '/audit'] as $hidden) {
            $this->assertStringNotContainsString('href="' . url($hidden) . '"', $html, "$hidden should be hidden from MENRO Staff");
        }
        foreach (['/dashboard', '/entries', '/generators', '/incidents', '/notifications',
                  '/compliance', '/collections', '/analytics', '/reports', '/violation-tickets',
                  '/archive'] as $shown) {
            $this->assertStringContainsString('href="' . url($shown) . '"', $html, "$shown should be shown");
        }
    }

    public function test_an_administrator_sees_every_section_in_the_sidebar(): void
    {
        $this->as('admin');
        $html = $this->get('/dashboard')->assertOk()->getContent();

        foreach (['/violation-tickets', '/compliance', '/collections', '/analytics', '/reports', '/users', '/audit', '/archive'] as $path) {
            $this->assertStringContainsString('href="' . url($path) . '"', $html, $path);
        }
    }

    public function test_a_staff_user_only_gets_edit_and_delete_on_their_own_entries(): void
    {
        $this->as('encoder');
        $mine = User::where('username', 'encoder')->value('user_id');
        $html = $this->get('/entries')->assertOk()->getContent();

        // Every row that offers a delete button is one this user encoded.
        preg_match_all('/wire:click="delete\((\d+)\)"/', $html, $m);
        foreach ($m[1] as $id) {
            $this->assertSame($mine, (int) \DB::table('waste_entries')->where('entry_id', $id)->value('encoded_by'));
        }
    }

    public function test_error_pages_are_branded_and_do_not_leak_internals(): void
    {
        $this->flushSession();
        $this->get('/no-such-page')->assertNotFound()->assertSee('Page not found')->assertSee('MENRO');

        $this->as('barangay');
        $this->get('/users')->assertForbidden()->assertSee("You don't have access to this page")->assertDontSee('Access denied.');
    }

    public function test_every_error_page_renders(): void
    {
        foreach ([403, 404, 419, 429, 500, 503] as $code) {
            $html = view("errors.$code")->render();
            $this->assertStringContainsString((string) $code, $html);
            $this->assertStringContainsString('Go back', $html);
            $this->assertMatchesRegularExpression('/<h1>\S[^<]*<\/h1>/', $html, "error $code has an empty heading");
        }
    }

    public function test_the_layout_has_landmarks_a_skip_link_and_named_icon_buttons(): void
    {
        $this->as('admin');
        $html = $this->get('/generators')->assertOk()->getContent();

        $this->assertStringContainsString('class="skip-link"', $html);
        $this->assertStringContainsString('<main class="page-content" id="main-content"', $html);
        $this->assertStringContainsString('aria-label="Main"', $html);
        $this->assertMatchesRegularExpression('/btn-icon-edit"[^>]*aria-label="Edit"|aria-label="Edit"[^>]*btn-icon-edit/', $html);
        $this->assertMatchesRegularExpression('/btn-icon-del"[^>]*aria-label="Delete"|aria-label="Delete"[^>]*btn-icon-del/', $html);
    }

    public function test_the_confirmation_dialog_is_on_every_page_and_buttons_use_it(): void
    {
        $this->as('admin');
        $html = $this->get('/generators')->assertOk()->getContent();

        $this->assertStringContainsString('id="confirm-dialog"', $html);
        $this->assertStringContainsString('data-confirm="Delete generator ', $html);
    }

    public function test_the_login_page_has_the_countdown_and_caps_lock_hint(): void
    {
        $this->flushSession();
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('id="caps-hint"', $html);
        $this->assertStringContainsString('Lockout countdown', $html);
    }

    public function test_a_locked_out_login_shows_the_alert_with_the_seconds_left(): void
    {
        $this->flushSession();
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', ['username' => 'admin', 'password' => 'wrong']);
        }

        $this->from('/login')->followingRedirects()->post('/login', ['username' => 'admin', 'password' => 'wrong'])
            ->assertSee('role="alert"', false)
            ->assertSee('login-error-line', false)
            ->assertSee('seconds');
    }

    public function test_a_livewire_action_without_inline_feedback_raises_a_toast_event(): void
    {
        $this->as('admin');
        $entry = \App\Models\WasteEntry::firstOrFail();

        $c = \Livewire\Livewire::test(\App\Http\Livewire\Entries\EntryIndex::class);
        \Illuminate\Support\Facades\DB::transaction(fn () => $c->call('delete', $entry->entry_id));

        $toasts = collect($c->payload['effects']['dispatches'] ?? [])->where('event', 'toast');
        $this->assertCount(1, $toasts);
        $this->assertSame('Entry deleted.', $toasts->first()['data']['message']);
        $this->assertSame('success', $toasts->first()['data']['type']);
    }

    public function test_a_component_that_shows_the_message_inline_does_not_also_toast(): void
    {
        $this->as('admin');
        $b = \App\Models\Barangay::create(['barangay_name' => 'Toast Probe', 'municipality' => 'Madrid', 'province' => 'Surigao del Sur']);

        $c = \Livewire\Livewire::test(\App\Http\Livewire\Barangays\BarangayIndex::class);
        \Illuminate\Support\Facades\DB::transaction(fn () => $c->call('deleteBrgy', $b->barangay_id));

        $this->assertStringContainsString('Barangay deleted.', (string) $c->lastRenderedDom);
        $this->assertCount(0, collect($c->payload['effects']['dispatches'] ?? [])->where('event', 'toast'));
    }
}
