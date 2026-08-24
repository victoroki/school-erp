<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Hard safety net: the suite must never run against the development
        // database. RefreshDatabase's migrate:fresh would drop every table in
        // it. The shell exports DB_DATABASE=school_management_system, so this
        // guard aborts loudly instead of wiping dev data.
        $db = config('database.connections.'.config('database.default').'.database');
        if ($db === 'school_management_system') {
            throw new \RuntimeException(
                'Refusing to run the test suite against the development database (school_management_system). '.
                'The suite must run against the isolated school_erp_test database.'
            );
        }
    }
}
