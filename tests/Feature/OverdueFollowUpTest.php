<?php

namespace Tests\Feature;

use App\Models\Inspection;
use App\Models\Notification;
use App\Models\User;
use App\Models\WasteGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * menro:flag-overdue-followups, run daily at 6 AM.
 *
 * It used to look for generators whose status was 'for_follow_up', which is an
 * inspection result and never a generator status ("For follow-up" puts the
 * generator at for_inspection), so it alerted staff that generators "were
 * marked non-compliant" while changing nothing.
 *
 * Writes run in a rolled-back transaction.
 */
class OverdueFollowUpTest extends TestCase
{
    use DatabaseTransactions;

    private function newGenerator(string $compliance = 'for_inspection', string $status = 'active'): WasteGenerator
    {
        $src = WasteGenerator::firstOrFail();

        return WasteGenerator::create([
            'generator_name'    => 'Follow-up Probe ' . uniqid(),
            'generator_type_id' => $src->generator_type_id,
            'barangay_id'       => $src->barangay_id,
            'compliance_status' => $compliance,
            'status'            => $status,
        ]);
    }

    private function inspect(WasteGenerator $g, string $date, string $result, ?string $followUp = null): Inspection
    {
        return Inspection::create([
            'generator_id'      => $g->generator_id,
            'inspection_date'   => $date,
            'inspector_id'      => User::where('username', 'admin')->value('user_id'),
            'compliance_status' => $result,
            'segregation_score' => 50,
            'next_follow_up'    => $followUp,
        ]);
    }

    private function daysAgo(int $n): string
    {
        return Carbon::today()->subDays($n)->toDateString();
    }

    private function runCommand(): void
    {
        $this->artisan('menro:flag-overdue-followups')->assertExitCode(0);
    }

    public function test_an_overdue_follow_up_marks_the_generator_non_compliant(): void
    {
        $g = $this->newGenerator();
        $this->inspect($g, $this->daysAgo(20), 'for_follow_up', $this->daysAgo(3));

        $this->runCommand();

        $this->assertSame('non_compliant', $g->fresh()->compliance_status);
    }

    public function test_a_follow_up_that_is_not_due_yet_changes_nothing(): void
    {
        $g = $this->newGenerator();
        $this->inspect($g, $this->daysAgo(2), 'for_follow_up', Carbon::today()->addDays(5)->toDateString());

        $this->runCommand();

        $this->assertSame('for_inspection', $g->fresh()->compliance_status);
    }

    public function test_a_follow_up_due_today_is_not_overdue_yet(): void
    {
        $g = $this->newGenerator();
        $this->inspect($g, $this->daysAgo(10), 'for_follow_up', Carbon::today()->toDateString());

        $this->runCommand();

        $this->assertSame('for_inspection', $g->fresh()->compliance_status);
    }

    public function test_a_newer_inspection_means_the_follow_up_took_place(): void
    {
        $g = $this->newGenerator('compliant');
        $this->inspect($g, $this->daysAgo(30), 'for_follow_up', $this->daysAgo(10));
        $this->inspect($g, $this->daysAgo(5), 'compliant');

        $this->runCommand();

        $this->assertSame('compliant', $g->fresh()->compliance_status);
    }

    public function test_a_status_set_by_hand_since_the_inspection_is_left_alone(): void
    {
        $g = $this->newGenerator('compliant');
        $this->inspect($g, $this->daysAgo(20), 'for_follow_up', $this->daysAgo(3));

        $this->runCommand();

        $this->assertSame('compliant', $g->fresh()->compliance_status);
    }

    public function test_inactive_and_archived_generators_are_skipped(): void
    {
        $inactive = $this->newGenerator('for_inspection', 'inactive');
        $this->inspect($inactive, $this->daysAgo(20), 'for_follow_up', $this->daysAgo(3));

        $archived = $this->newGenerator();
        $this->inspect($archived, $this->daysAgo(20), 'for_follow_up', $this->daysAgo(3));
        $archived->delete();

        $this->runCommand();

        $this->assertSame('for_inspection', $inactive->fresh()->compliance_status);
        $this->assertSame('for_inspection', WasteGenerator::withTrashed()->find($archived->generator_id)->compliance_status);
    }

    public function test_staff_are_told_how_many_generators_were_marked(): void
    {
        $g = $this->newGenerator();
        $this->inspect($g, $this->daysAgo(20), 'for_follow_up', $this->daysAgo(3));
        $admin = User::where('username', 'admin')->value('user_id');
        $before = now()->subSecond();

        $this->runCommand();

        $alert = Notification::where('user_id', $admin)
            ->where('title', 'Overdue Follow-Up Alert')
            ->where('created_at', '>=', $before)
            ->latest('notification_id')
            ->first();
        $this->assertNotNull($alert);
        $this->assertMatchesRegularExpression('/^\d+ generator\(s\) have overdue follow-up inspections and were marked non-compliant\.$/',
            $alert->message);
    }

    public function test_a_second_run_finds_nothing_left_to_mark(): void
    {
        $g = $this->newGenerator();
        $this->inspect($g, $this->daysAgo(20), 'for_follow_up', $this->daysAgo(3));

        $this->runCommand();
        $this->artisan('menro:flag-overdue-followups')
            ->expectsOutput('No overdue follow-ups found.')
            ->assertExitCode(0);

        $this->assertSame('non_compliant', $g->fresh()->compliance_status);
    }
}
