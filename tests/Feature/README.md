# Feature Tests Documentation

This directory contains comprehensive feature tests for the Filament Practice Project. These tests cover all major functionality and ensure the application works correctly.

## Test Files Overview

### Core Model Tests

1. **UserTest.php** - Tests for User model functionality
   - User creation and validation
   - Role and permission relationships
   - Authentication features
   - Password hashing
   - Avatar functionality

2. **PaperTest.php** - Tests for Paper model functionality
   - Paper creation and validation
   - File URL generation
   - Relationships with users, countries, and paper types
   - Subject and tag associations
   - Boolean field casting

3. **PersonTest.php** - Tests for Person model functionality
   - Person creation and validation
   - Location relationships (Country, Province, City)
   - Contact information handling
   - Education and experience relationships
   - Gender and personal data validation

4. **LocationTest.php** - Tests for location hierarchy
   - Country, Province, City model functionality
   - Hierarchical relationships
   - Person-location associations
   - Data validation and constraints

### System Tests

5. **RolePermissionTest.php** - Tests for role-based access control
   - Role and permission creation
   - User-role assignments
   - Permission inheritance through roles
   - Complex permission scenarios
   - CRUD operations for roles and permissions

6. **OpenAiServiceTest.php** - Tests for AI service functionality
   - OpenAI API integration
   - Fallback tag extraction
   - Caching mechanisms
   - Error handling and logging
   - Tag parsing and formatting

### Admin Panel Tests

7. **FilamentAdminTest.php** - Tests for Filament admin panel
   - Admin authentication and authorization
   - CRUD operations for all resources
   - Role and permission management
   - Data export functionality
   - Search and filtering
   - Bulk operations
   - Dashboard statistics

## Running the Tests

### Run All Feature Tests
```bash
php artisan test tests/Feature/
```

### Run Specific Test File
```bash
php artisan test tests/Feature/UserTest.php
```

### Run Specific Test Method
```bash
php artisan test --filter test_user_can_be_created_with_valid_data
```

### Run Tests with Coverage
```bash
php artisan test --coverage
```

## Test Setup

The tests use the following setup:

- **RefreshDatabase**: Each test runs with a fresh database
- **Factories**: Model factories for creating test data
- **Seeders**: Basic data seeding for consistent test environment
- **Mocking**: HTTP requests and external services are mocked

## Test Data

Tests create the following basic data:
- Countries (Iran, United States)
- Paper Types (Research Paper, Review Paper)
- Admin roles and permissions
- Test users with various roles

## Coverage Areas

These tests cover:

### ✅ Model Functionality
- CRUD operations
- Relationships and associations
- Data validation
- Attribute casting
- Accessors and mutators

### ✅ Authentication & Authorization
- User authentication
- Role-based access control
- Permission checking
- Admin panel access

### ✅ API Integration
- OpenAI service integration
- Fallback mechanisms
- Error handling
- Caching

### ✅ Admin Panel
- Filament resource management
- Form submissions
- Data exports
- Search and filtering
- Bulk operations

### ✅ Data Integrity
- Foreign key constraints
- Unique constraints
- Required field validation
- Data relationships

## Best Practices

1. **Isolation**: Each test is independent and doesn't rely on others
2. **Cleanup**: Tests clean up after themselves using RefreshDatabase
3. **Realistic Data**: Tests use realistic test data that mirrors production
4. **Error Testing**: Tests cover both success and failure scenarios
5. **Performance**: Tests are optimized for speed and reliability

## Troubleshooting

### Common Issues

1. **Database Connection**: Ensure testing database is configured
2. **Factory Issues**: Check that all required factories exist
3. **Seeder Problems**: Verify seeders are working correctly
4. **Permission Errors**: Ensure proper role/permission setup

### Debug Mode
Run tests with verbose output:
```bash
php artisan test --verbose
```

## Contributing

When adding new features:
1. Create corresponding feature tests
2. Update this documentation
3. Ensure all tests pass
4. Add appropriate test data and factories
