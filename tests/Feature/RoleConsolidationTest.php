<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Guards the 2026-10 consolidation of the six roles down to two.
 *
 * The retired names are not just unused — they are actively misleading if one
 * survives, because every authorization check in this app compares role_name
 * strings. A leftover 'Field Inspector' in a canAccess() call matches nobody
 * today and silently grants access the day someone recreates that role.
 */
class RoleConsolidationTest extends TestCase
{
    private const RETIRED = [
        'MENRO Officer', 'Data Encoder', 'Field Inspector', 'Barangay User', 'Report Viewer',
    ];

    public function test_the_database_holds_exactly_the_two_roles(): void
    {
        $this->assertSame(
            ['MENRO Staff', 'System Administrator'],
            DB::table('roles')->orderBy('role_name')->pluck('role_name')->all()
        );
    }

    public function test_every_user_holds_one_of_the_two_roles(): void
    {
        $orphans = DB::table('users')
            ->whereNotIn('role_id', DB::table('roles')->select('role_id'))
            ->count();

        $this->assertSame(0, $orphans, 'every user must point at a surviving role');
    }

    /**
     * docs/ and the .py manuscript generators are excluded on purpose: the first
     * are dated records of past findings, the second are maintained separately.
     */
    public function test_no_source_file_still_names_a_retired_role(): void
    {
        $hits = [];

        foreach (['app', 'routes', 'resources/views', 'database/seeders'] as $dir) {
            $path = base_path($dir);

            if (! is_dir($path)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());

                foreach (self::RETIRED as $role) {
                    // The quoted form only: a role name is a string literal when
                    // it is load-bearing, and prose explaining what was retired
                    // is not a bug.
                    if (str_contains($contents, "'" . $role . "'")
                        || str_contains($contents, '"' . $role . '"')) {
                        $hits[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname()) . " names '{$role}'";
                    }
                }
            }
        }

        $this->assertSame([], $hits, "retired role names left in the source:\n" . implode("\n", $hits));
    }
}
