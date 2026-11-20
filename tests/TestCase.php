<?php

namespace Tests;

use App\Models\Setting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();
    }

    protected function tearDown(): void
    {
        Setting::clearCache();
        parent::tearDown();
    }
}
