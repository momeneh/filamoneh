<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure clean database state
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        
        // Disable rate limiting for tests
        $this->app['config']->set('filament.auth.rate_limiting.enabled', false);
        
        // Only disable foreign key checks for MySQL, not SQLite
        if ($this->app['config']->get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
    }

    protected function tearDown(): void
    {
        // Re-enable foreign key checks only for MySQL
        if ($this->app['config']->get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        // Ensure all transactions are rolled back
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        
        parent::tearDown();
    }
    /*
    * Helper method to create an admin user with proper permissions
     */
    protected function createAdminUser()
    {
        $admin = \App\Models\User::factory()->create();
        $adminRole = \App\Models\Role::create(['title' => 'testAdmin']);
        // Create necessary permissions
        $permissions = \App\Models\Permission::All();

        foreach ($permissions as $permission) {
            // $permission = \App\Models\Permission::firstOrCreate(['name' => $permissionName]);
            $adminRole->permissions()->attach($permission->id);
        }
        $admin->roles()->attach($adminRole->id);

        return $admin;
    }

    /**
     * Create a regular user for testing
     */
    protected function createUser()
    {
        return \App\Models\User::factory()->create();
    }

    /**
     * Seed the database with basic data for tests
     */
    protected function seedBasicData()
    {
        // Create basic countries for testing
        \App\Models\Country::create(['title' => 'Iran']);
        \App\Models\Country::create(['title' => 'United States']);
        
        // Create basic paper types for testing
        \App\Models\PaperType::create(['title' => 'Research Paper']);
        \App\Models\PaperType::create(['title' => 'Review Paper']);
    }
}
