# PHPUnit Attributes Migration

## Overview
This document explains the migration from PHPUnit docblock annotations to PHP attributes, which is required for PHPUnit 12 compatibility.

## What Changed

### Before (Deprecated)
```php
/** @test */
public function user_can_be_created_with_valid_data()
{
    // test code
}
```

### After (Modern PHP Attributes)
```php
#[Test]
public function user_can_be_created_with_valid_data()
{
    // test code
}
```

## Files Updated

### Feature Tests
- ✅ `tests/Feature/UserTest.php` - 11 test methods updated
- ✅ `tests/Feature/PaperTest.php` - 15 test methods updated
- ✅ `tests/Feature/PersonTest.php` - 12 test methods updated
- ✅ `tests/Feature/LocationTest.php` - 15 test methods updated
- ✅ `tests/Feature/RolePermissionTest.php` - 17 test methods updated
- ✅ `tests/Feature/OpenAiServiceTest.php` - 16 test methods updated
- ✅ `tests/Feature/FilamentAdminTest.php` - 15 test methods updated
- ✅ `tests/Feature/ExampleTest.php` - 1 test method updated

### Unit Tests
- ✅ `tests/Unit/ExampleTest.php` - 1 test method updated
- ✅ `tests/Unit/OpenAiServiceTest.php` - Already using attributes

## Required Import
All test files now include the PHPUnit Test attribute import:
```php
use PHPUnit\Framework\Attributes\Test;
```

## Benefits of Migration

1. **Future Compatibility** - Compatible with PHPUnit 12
2. **Modern PHP Syntax** - Uses PHP 8+ attributes instead of docblocks
3. **Better IDE Support** - Better autocomplete and refactoring support
4. **Cleaner Code** - More concise and readable
5. **Performance** - Slightly better performance as attributes are native PHP

## Migration Pattern

### Automatic Migration Command
If you have more tests to migrate, you can use this pattern:

```bash
# Find all files with @test annotations
find tests/ -name "*.php" -exec grep -l "/\*\* @test \*/" {} \;

# Replace in all files (use with caution)
find tests/ -name "*.php" -exec sed -i '' 's|    /\*\* @test \*/|    #[Test]|g' {} \;
```

### Manual Migration Steps
1. Add the import: `use PHPUnit\Framework\Attributes\Test;`
2. Replace `/** @test */` with `#[Test]`
3. Run tests to verify everything works

## Verification

Run the tests to ensure the migration was successful:
```bash
php artisan test --verbose
```

You should no longer see warnings about deprecated metadata in doc-comments.

## Additional PHPUnit Attributes

### Test Data Providers
```php
#[DataProvider('userDataProvider')]
#[Test]
public function test_user_creation($userData)
{
    // test code
}

public static function userDataProvider(): array
{
    return [
        'valid user' => [['name' => 'John', 'email' => 'john@example.com']],
        'admin user' => [['name' => 'Admin', 'email' => 'admin@example.com']],
    ];
}
```

### Skipped Tests
```php
#[Test]
#[Skip('This test is temporarily disabled')]
public function test_feature_under_development()
{
    // test code
}
```

### Grouped Tests
```php
#[Test]
#[Group('integration')]
#[Group('slow')]
public function test_database_integration()
{
    // test code
}
```

### Test Dependencies
```php
#[Test]
public function test_first_step()
{
    // test code
}

#[Test]
#[Depends('test_first_step')]
public function test_second_step()
{
    // depends on test_first_step
}
```

## Best Practices

1. **Always use attributes** for new tests
2. **Migrate existing tests** when touching them
3. **Use descriptive test names** with the `test_` prefix or `#[Test]` attribute
4. **Group related tests** using `#[Group]` attributes
5. **Use data providers** for parameterized tests

## Compatibility

- ✅ PHPUnit 9.x - Supports both attributes and docblocks
- ✅ PHPUnit 10.x - Supports both attributes and docblocks (with deprecation warnings)
- ✅ PHPUnit 11.x - Supports both attributes and docblocks (with deprecation warnings)
- ✅ PHPUnit 12.x - Attributes only (docblocks removed)

## Resources

- [PHPUnit Attributes Documentation](https://docs.phpunit.de/en/10.5/attributes.html)
- [PHP Attributes Documentation](https://www.php.net/manual/en/language.attributes.php)
- [Migration Guide](https://github.com/sebastianbergmann/phpunit/blob/10.5/ChangeLog-10.5.md)
