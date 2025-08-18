#!/bin/bash

# Test script to verify package installation
set -e

echo "Testing Laravel App Developer package installation..."

# Create a temporary directory for testing
TEST_DIR="/tmp/test-laravel-app-developer-$(date +%s)"
mkdir -p "$TEST_DIR"
cd "$TEST_DIR"

echo "Created test directory: $TEST_DIR"

# Create a minimal Laravel project structure for testing
echo "Creating minimal test Laravel project..."
cat > composer.json << 'EOF'
{
    "name": "test/laravel-app",
    "type": "project",
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0"
    },
    "require-dev": {
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
EOF

# Try to add our package with different methods
echo ""
echo "=== Testing package installation methods ==="

echo ""
echo "1. Testing via Packagist (normal installation):"
echo "composer require stafe-group-ab/laravel-app-developer --dev --dry-run"
if composer require stafe-group-ab/laravel-app-developer --dev --dry-run 2>&1; then
    echo "✅ Package found on Packagist!"
else
    echo "❌ Package not found on Packagist"
fi

echo ""
echo "2. Testing via GitHub repository directly:"
echo "composer require stafe-group-ab/laravel-app-developer:dev-main --dev --dry-run"
if composer require stafe-group-ab/laravel-app-developer:dev-main --dev --dry-run 2>&1; then
    echo "✅ Package can be installed from GitHub!"
else
    echo "❌ Package cannot be installed from GitHub"
fi

echo ""
echo "3. Testing with VCS repository:"
cat > composer-vcs.json << 'EOF'
{
    "name": "test/laravel-app",
    "type": "project",
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0"
    },
    "require-dev": {
        "stafe-group-ab/laravel-app-developer": "dev-main"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/STAFE-GROUP-AB/Laravel-App-Developer"
        }
    ],
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
EOF

echo "Testing with VCS repository configuration..."
cp composer-vcs.json composer.json
if composer install --dry-run 2>&1; then
    echo "✅ Package can be installed with VCS repository!"
else
    echo "❌ Package cannot be installed with VCS repository"
fi

echo ""
echo "=== Test completed ==="
echo "Test directory: $TEST_DIR"
echo "(You can manually inspect the test directory if needed)"

# Clean up
echo "Cleaning up test directory..."
rm -rf "$TEST_DIR"
echo "Done!"