# Fixing "There is already an active transaction" Error

## Problem Description
The "There is already an active transaction" error occurs in Laravel tests when:
1. Multiple database transactions are nested
2. Previous transactions aren't properly rolled back
3. Database connections aren't properly reset between tests
4. Using `RefreshDatabase` with certain database configurations

## Solutions Applied

### 1. Enhanced TestCase.php
- Added transaction cleanup in `setUp()` and `tearDown()`
- Added foreign key constraint handling
- Proper database state management

### 2. Database Configuration
- Created `config/testing.php` for test-specific database settings
- Configured SQLite in-memory database for faster testing
- Disabled foreign key constraints during testing

### 3. Test File Updates
- Added transaction rollback in each test file's `setUp()` method
- Ensured clean database state before each test

## Additional Solutions

### Option 1: Use DatabaseTransactions Instead of RefreshDatabase
```php
use Illuminate\Foundation\Testing\DatabaseTransactions;

class YourTest extends TestCase
{
    use DatabaseTransactions;
    // This rolls back transactions instead of refreshing the database
}
```

### Option 2: Use DatabaseMigrations
```php
use Illuminate\Foundation\Testing\DatabaseMigrations;

class YourTest extends TestCase
{
    use DatabaseMigrations;
    // This runs migrations for each test
}
```

### Option 3: Manual Database Cleanup
```php
protected function setUp(): void
{
    parent::setUp();
    
    // Roll back any existing transactions
    while (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
    
    // Truncate tables manually
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    // Your cleanup code here
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
}
```

## Recommended Database Configuration for Testing

### .env.testing
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

### phpunit.xml (already configured)
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

## Running Tests with Transaction Fix

### Run Tests with Verbose Output
```bash
php artisan test --verbose
```

### Run Specific Test File
```bash
php artisan test tests/Feature/UserTest.php --verbose
```

### Run Tests with Process Isolation
```bash
php artisan test --processes=1
```

## Troubleshooting Steps

### 1. Check Database Connection
```bash
php artisan tinker
DB::connection()->getPdo();
```

### 2. Verify Transaction Level
```php
// In your test
dd(DB::transactionLevel());
```

### 3. Check for Model Observers
Make sure model observers aren't creating nested transactions:
```php
// In your model
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($model) {
        // Avoid DB transactions here
    });
}
```

### 4. Disable Query Logging
```php
// In TestCase setUp()
DB::disableQueryLog();
```

### 5. Use Fresh Application for Each Test
```php
protected function setUp(): void
{
    parent::setUp();
    $this->refreshApplication();
}
```

## Alternative: Use DatabaseTesting Trait
```php
use Illuminate\Foundation\Testing\DatabaseTesting;

class YourTest extends TestCase
{
    use DatabaseTesting;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }
}
```

## Best Practices

1. **Use SQLite in-memory** for faster tests
2. **Avoid nested transactions** in model events
3. **Clean up after each test** properly
4. **Use factories** instead of direct database calls
5. **Mock external services** to avoid side effects
6. **Run tests in isolation** when possible

## If Problems Persist

### Option 1: Reset Database Connection
```php
protected function tearDown(): void
{
    DB::disconnect();
    parent::tearDown();
}
```

### Option 2: Use Separate Database
```php
// In TestCase
protected function setUp(): void
{
    parent::setUp();
    config(['database.default' => 'testing']);
    $this->artisan('migrate:fresh');
}
```

### Option 3: Disable Transactions Completely
```php
// In config/database.php testing connection
'options' => [
    PDO::ATTR_AUTOCOMMIT => 1,
],
```
