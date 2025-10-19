#!/bin/bash

# Filament Practice Project - Test Runner Script
# This script runs the comprehensive test suite for the project

echo "🧪 Starting Filament Practice Project Test Suite"
echo "================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Please run this script from the Laravel project root directory"
    exit 1
fi

# Check if Docker is available (since the project uses Docker)
if command -v docker-compose &> /dev/null; then
    print_status "Docker Compose detected - using Docker environment"
    RUNNER="docker-compose exec app"
elif command -v php &> /dev/null; then
    print_status "PHP detected - using local environment"
    RUNNER=""
else
    print_error "Neither Docker Compose nor PHP found. Please install one of them."
    exit 1
fi

# Function to run tests
run_tests() {
    local test_path="$1"
    local description="$2"
    
    print_status "Running $description..."
    
    if [ -n "$RUNNER" ]; then
        $RUNNER php artisan test "$test_path" --verbose
    else
        php artisan test "$test_path" --verbose
    fi
    
    local exit_code=$?
    
    if [ $exit_code -eq 0 ]; then
        print_success "$description completed successfully"
    else
        print_error "$description failed with exit code $exit_code"
        return $exit_code
    fi
}

# Function to run specific test categories
run_feature_tests() {
    print_status "🚀 Running Feature Tests..."
    echo "----------------------------------------"
    
    # Run each feature test file individually for better feedback
    run_tests "tests/Feature/UserTest.php" "User Model Tests"
    run_tests "tests/Feature/PaperTest.php" "Paper Model Tests"
    run_tests "tests/Feature/PersonTest.php" "Person Model Tests"
    run_tests "tests/Feature/LocationTest.php" "Location Model Tests"
    run_tests "tests/Feature/RolePermissionTest.php" "Role & Permission Tests"
    run_tests "tests/Feature/OpenAiServiceTest.php" "OpenAI Service Tests"
    run_tests "tests/Feature/FilamentAdminTest.php" "Filament Admin Panel Tests"
}

run_unit_tests() {
    print_status "🔬 Running Unit Tests..."
    echo "----------------------------------------"
    
    run_tests "tests/Unit/" "Unit Tests"
}

run_all_tests() {
    print_status "🎯 Running Complete Test Suite..."
    echo "========================================"
    
    run_tests "tests/" "All Tests"
}

# Function to show test coverage
show_coverage() {
    print_status "📊 Generating Test Coverage Report..."
    echo "----------------------------------------"
    
    if [ -n "$RUNNER" ]; then
        $RUNNER php artisan test --coverage --min=80
    else
        php artisan test --coverage --min=80
    fi
}

# Function to setup test environment
setup_tests() {
    print_status "⚙️  Setting up test environment..."
    echo "----------------------------------------"
    
    if [ -n "$RUNNER" ]; then
        print_status "Clearing caches..."
        $RUNNER php artisan config:clear
        $RUNNER php artisan cache:clear
        $RUNNER php artisan view:clear
        
        print_status "Running migrations..."
        $RUNNER php artisan migrate:fresh --env=testing
        
        print_status "Seeding test data..."
        $RUNNER php artisan db:seed --env=testing
        
        print_status "Optimizing for testing..."
        $RUNNER php artisan optimize:clear
    else
        print_status "Clearing caches..."
        php artisan config:clear
        php artisan cache:clear
        php artisan view:clear
        
        print_status "Running migrations..."
        php artisan migrate:fresh --env=testing
        
        print_status "Seeding test data..."
        php artisan db:seed --env=testing
        
        print_status "Optimizing for testing..."
        php artisan optimize:clear
    fi
    
    print_success "Test environment setup complete"
}

# Function to fix transaction issues
fix_transaction_issues() {
    print_status "🔧 Fixing transaction issues..."
    echo "----------------------------------------"
    
    if [ -n "$RUNNER" ]; then
        print_status "Resetting database connections..."
        $RUNNER php artisan tinker --execute="DB::disconnect();"
        
        print_status "Clearing all caches..."
        $RUNNER php artisan cache:clear
        $RUNNER php artisan config:clear
        $RUNNER php artisan route:clear
        $RUNNER php artisan view:clear
    else
        print_status "Resetting database connections..."
        php artisan tinker --execute="DB::disconnect();"
        
        print_status "Clearing all caches..."
        php artisan cache:clear
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear
    fi
    
    print_success "Transaction issues fixed"
}

# Main menu
show_menu() {
    echo ""
    echo "📋 Test Suite Options:"
    echo "====================="
    echo "1) Run Feature Tests Only"
    echo "2) Run Unit Tests Only"
    echo "3) Run All Tests"
    echo "4) Run Tests with Coverage"
    echo "5) Setup Test Environment"
    echo "6) Fix Transaction Issues"
    echo "7) Run Specific Test File"
    echo "8) Exit"
    echo ""
}

# Handle user input
handle_choice() {
    case $1 in
        1)
            run_feature_tests
            ;;
        2)
            run_unit_tests
            ;;
        3)
            run_all_tests
            ;;
        4)
            show_coverage
            ;;
        5)
            setup_tests
            ;;
        6)
            fix_transaction_issues
            ;;
        7)
            echo -n "Enter test file path (e.g., tests/Feature/UserTest.php): "
            read test_file
            if [ -f "$test_file" ]; then
                run_tests "$test_file" "Specific Test File: $test_file"
            else
                print_error "Test file not found: $test_file"
            fi
            ;;
        8)
            print_success "Goodbye! 👋"
            exit 0
            ;;
        *)
            print_error "Invalid option. Please choose 1-8."
            ;;
    esac
}

# Main execution
main() {
    # If arguments provided, run directly
    if [ $# -gt 0 ]; then
        case "$1" in
            "feature")
                run_feature_tests
                ;;
            "unit")
                run_unit_tests
                ;;
            "all")
                run_all_tests
                ;;
            "coverage")
                show_coverage
                ;;
            "setup")
                setup_tests
                ;;
            *)
                print_error "Unknown option: $1"
                echo "Usage: $0 [feature|unit|all|coverage|setup]"
                exit 1
                ;;
        esac
    else
        # Interactive mode
        while true; do
            show_menu
            echo -n "Choose an option (1-8): "
            read choice
            handle_choice "$choice"
            echo ""
            echo "Press Enter to continue..."
            read
        done
    fi
}

# Run main function with all arguments
main "$@"
