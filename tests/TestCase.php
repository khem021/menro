<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // The array cache driver lives for the whole test process, so without
        // this a test inherits whatever the previous one cached. That matters
        // in two places: the dashboard/lookup caches, which would otherwise
        // serve one test's rows to the next, and the sign-in rate limiter,
        // where failed-login attempts would accumulate across tests until the
        // lockout trips and later tests fail for the wrong reason.
        Cache::flush();
    }
}
