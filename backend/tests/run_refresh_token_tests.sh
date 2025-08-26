#!/bin/bash

# Comprehensive test runner for refresh token feature
# This script runs all refresh token related tests and provides a summary

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test categories
declare -A TEST_CATEGORIES=(
    ["Entity Tests"]="tests/Entity/RefreshTokenTest.php"
    ["Repository Tests"]="tests/Repository/RefreshTokenRepositoryTest.php"
    ["Controller Tests"]="tests/Controller/TokenControllerTest.php"
    ["Event Listener Tests"]="tests/EventListener/AttachRefreshTokenOnLoginListenerTest.php"
    ["Integration Tests"]="tests/Integration/RefreshTokenIntegrationTest.php"
    ["Security Tests"]="tests/Security/RefreshTokenSecurityTest.php"
    ["Authentication Tests"]="tests/Security/AuthenticationTest.php"
    ["Performance Tests"]="tests/Performance/RefreshTokenPerformanceTest.php"
)

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Refresh Token Comprehensive Test Suite${NC}"
echo -e "${BLUE}========================================${NC}"
echo

# Check if PHPUnit is available
if ! command -v ./vendor/bin/phpunit &> /dev/null; then
    echo -e "${RED}Error: PHPUnit not found. Please run 'composer install' first.${NC}"
    exit 1
fi

# Initialize counters
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0
FAILED_CATEGORIES=()

# Function to run a test category
run_test_category() {
    local category="$1"
    local test_file="$2"
    
    echo -e "${YELLOW}Running: $category${NC}"
    echo "File: $test_file"
    
    if [ ! -f "$test_file" ]; then
        echo -e "${RED}  ❌ Test file not found: $test_file${NC}"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        FAILED_CATEGORIES+=("$category (file not found)")
        echo
        return
    fi
    
    # Run the test and capture output
    if output=$(./vendor/bin/phpunit --colors=always --testdox "$test_file" 2>&1); then
        echo -e "${GREEN}  ✅ $category - PASSED${NC}"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo -e "${RED}  ❌ $category - FAILED${NC}"
        echo -e "${RED}Output:${NC}"
        echo "$output" | sed 's/^/    /'
        FAILED_TESTS=$((FAILED_TESTS + 1))
        FAILED_CATEGORIES+=("$category")
    fi
    echo
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
}

# Run all test categories
for category in "${!TEST_CATEGORIES[@]}"; do
    run_test_category "$category" "${TEST_CATEGORIES[$category]}"
done

# Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Test Summary${NC}"
echo -e "${BLUE}========================================${NC}"
echo -e "Total Categories: $TOTAL_TESTS"
echo -e "${GREEN}Passed: $PASSED_TESTS${NC}"
echo -e "${RED}Failed: $FAILED_TESTS${NC}"

if [ $FAILED_TESTS -gt 0 ]; then
    echo
    echo -e "${RED}Failed Categories:${NC}"
    for failed_category in "${FAILED_CATEGORIES[@]}"; do
        echo -e "  ${RED}- $failed_category${NC}"
    done
fi

echo
echo -e "${BLUE}========================================${NC}"

# Coverage report (if available)
if command -v ./vendor/bin/phpunit &> /dev/null; then
    echo -e "${YELLOW}Generating coverage report for refresh token files...${NC}"
    
    # Define the files we want coverage for
    COVERAGE_FILES=(
        "src/Entity/RefreshToken.php"
        "src/Repository/RefreshTokenRepository.php" 
        "src/Controller/Api/TokenController.php"
        "src/EventListener/AttachRefreshTokenOnLoginListener.php"
    )
    
    # Check if all coverage files exist
    ALL_FILES_EXIST=true
    for file in "${COVERAGE_FILES[@]}"; do
        if [ ! -f "$file" ]; then
            echo -e "${RED}Coverage file not found: $file${NC}"
            ALL_FILES_EXIST=false
        fi
    done
    
    if [ "$ALL_FILES_EXIST" = true ]; then
        echo "Running coverage analysis..."
        # Note: This is a basic coverage command. Adjust based on your PHPUnit configuration
        ./vendor/bin/phpunit --coverage-text --whitelist=src/ tests/Entity/RefreshTokenTest.php tests/Repository/RefreshTokenRepositoryTest.php tests/Controller/TokenControllerTest.php 2>/dev/null | grep -A 20 "Code Coverage Report" || echo "Coverage report not available (requires Xdebug)"
    fi
fi

echo
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Test Recommendations${NC}"  
echo -e "${BLUE}========================================${NC}"
echo
echo -e "${YELLOW}Security Recommendations:${NC}"
echo "• Implement rate limiting for refresh token endpoints"
echo "• Add logging for security events (failed attempts, etc.)"
echo "• Consider implementing refresh token blacklisting"
echo "• Monitor for suspicious patterns in refresh token usage"
echo
echo -e "${YELLOW}Performance Recommendations:${NC}"
echo "• Set up database indexes on refresh_tokens.token column"
echo "• Implement periodic cleanup of expired tokens"
echo "• Consider using Redis for high-frequency refresh token operations"
echo "• Monitor refresh token database performance"
echo
echo -e "${YELLOW}Testing Recommendations:${NC}"
echo "• Run these tests in CI/CD pipeline"
echo "• Add integration tests with frontend applications"
echo "• Consider load testing with multiple concurrent users"
echo "• Test refresh token behavior across different environments"

# Exit with error code if any tests failed
if [ $FAILED_TESTS -gt 0 ]; then
    echo
    echo -e "${RED}Some tests failed. Please fix the issues before deploying.${NC}"
    exit 1
else
    echo
    echo -e "${GREEN}All refresh token tests passed! 🎉${NC}"
    exit 0
fi