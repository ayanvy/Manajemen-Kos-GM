<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSafetyTest extends TestCase
{
    public function test_tests_run_in_testing_environment(): void
    {
        $this->assertSame('testing', app()->environment());
    }

    
public function test_default_connection_is_sqlite_in_memory(): void
{
    $this->assertSame('testing', app()->environment());
    $this->assertSame('sqlite', config('database.default'));
    $this->assertSame(
        ':memory:',
        config('database.connections.sqlite.database')
    );
    $this->assertSame(
        'sqlite',
        DB::connection()->getDriverName()
    );
    $this->assertEmpty(config('database.connections.sqlite.url'));
    $this->assertEmpty(config('database.connections.'.config('database.default').'.url'));
}

}
