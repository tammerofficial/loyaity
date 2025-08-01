<?php
/**
 * Installation Test Script
 * سكريبت اختبار التثبيت
 * 
 * Run this after installation to verify everything works correctly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Colors for CLI output
$colors = [
    'red' => "\033[0;31m",
    'green' => "\033[0;32m", 
    'yellow' => "\033[1;33m",
    'blue' => "\033[0;34m",
    'purple' => "\033[0;35m",
    'reset' => "\033[0m"
];

function colorOutput($text, $color = 'reset') {
    global $colors;
    if (php_sapi_name() === 'cli') {
        return $colors[$color] . $text . $colors['reset'];
    }
    return $text;
}

function testPrint($message, $status = 'info') {
    $statusColors = [
        'success' => 'green',
        'error' => 'red', 
        'warning' => 'yellow',
        'info' => 'blue'
    ];
    
    $icon = [
        'success' => '✅',
        'error' => '❌',
        'warning' => '⚠️',
        'info' => 'ℹ️'
    ];
    
    $color = $statusColors[$status] ?? 'reset';
    echo colorOutput($icon[$status] . ' ' . $message, $color) . "\n";
}

function runTest($testName, $callable) {
    echo colorOutput("\n🧪 Testing: $testName", 'purple') . "\n";
    echo str_repeat('-', 50) . "\n";
    
    try {
        $result = $callable();
        if ($result === true) {
            testPrint("$testName passed", 'success');
            return true;
        } else {
            testPrint("$testName failed: " . ($result ?: 'Unknown error'), 'error');
            return false;
        }
    } catch (Exception $e) {
        testPrint("$testName failed with exception: " . $e->getMessage(), 'error');
        return false;
    }
}

// Start testing
echo colorOutput("🔬 Laravel Installation Test Suite", 'blue') . "\n";
echo colorOutput("=" . str_repeat("=", 49), 'blue') . "\n";

$tests = [];
$passed = 0;
$failed = 0;

// Test 1: Check if we're in a Laravel project
$tests['Laravel Project'] = function() {
    return file_exists('artisan') && file_exists('composer.json');
};

// Test 2: Check PHP version
$tests['PHP Version'] = function() {
    $version = PHP_VERSION;
    if (version_compare($version, '8.2.0', '>=')) {
        testPrint("PHP version: $version", 'info');
        return true;
    } else {
        return "PHP 8.2+ required, found: $version";
    }
};

// Test 3: Check PHP extensions
$tests['PHP Extensions'] = function() {
    $required = ['curl', 'fileinfo', 'gd', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml', 'zip'];
    $missing = [];
    
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }
    
    if (empty($missing)) {
        testPrint("All required extensions loaded", 'info');
        return true;
    } else {
        return "Missing extensions: " . implode(', ', $missing);
    }
};

// Test 4: Check .env file
$tests['.env Configuration'] = function() {
    if (!file_exists('.env')) {
        return ".env file not found";
    }
    
    $env = file_get_contents('.env');
    $required = ['APP_KEY', 'DB_DATABASE', 'DB_USERNAME'];
    $missing = [];
    
    foreach ($required as $key) {
        if (strpos($env, $key . '=') === false) {
            $missing[] = $key;
        }
    }
    
    if (empty($missing)) {
        testPrint(".env file configured", 'info');
        return true;
    } else {
        return "Missing .env keys: " . implode(', ', $missing);
    }
};

// Test 5: Check file permissions
$tests['File Permissions'] = function() {
    $dirs = ['storage', 'bootstrap/cache'];
    $issues = [];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            $issues[] = "$dir directory not found";
        } elseif (!is_writable($dir)) {
            $issues[] = "$dir is not writable";
        }
    }
    
    if (empty($issues)) {
        testPrint("All directories writable", 'info');
        return true;
    } else {
        return implode(', ', $issues);
    }
};

// Test 6: Check Composer dependencies
$tests['Composer Dependencies'] = function() {
    if (!is_dir('vendor')) {
        return "vendor directory not found - run 'composer install'";
    }
    
    if (!file_exists('vendor/autoload.php')) {
        return "Composer autoloader not found";
    }
    
    testPrint("Composer dependencies installed", 'info');
    return true;
};

// Test 7: Test Laravel application
$tests['Laravel Application'] = function() {
    if (!file_exists('vendor/autoload.php')) {
        return "Autoloader not found";
    }
    
    try {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        
        if ($app instanceof Illuminate\Foundation\Application) {
            testPrint("Laravel application boots successfully", 'info');
            return true;
        } else {
            return "Failed to boot Laravel application";
        }
    } catch (Exception $e) {
        return "Laravel boot error: " . $e->getMessage();
    }
};

// Test 8: Database connection (if available)
$tests['Database Connection'] = function() {
    if (!file_exists('vendor/autoload.php')) {
        return "Skip - Composer not installed";
    }
    
    try {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        
        if (!($app instanceof Illuminate\Foundation\Application)) {
            return "Failed to boot Laravel application";
        }
        
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        // Try to get database connection
        $connection = $app->make('db');
        $connection->getPdo();
        
        testPrint("Database connected successfully", 'info');
        return true;
    } catch (Exception $e) {
        testPrint("Database connection failed: " . $e->getMessage(), 'warning');
        return "Database not configured or not accessible";
    }
};

// Test 9: Check routes
$tests['Routes Configuration'] = function() {
    $routeFiles = ['routes/web.php', 'routes/api.php'];
    $found = 0;
    
    foreach ($routeFiles as $file) {
        if (file_exists($file)) {
            $found++;
        }
    }
    
    if ($found > 0) {
        testPrint("Route files found: $found", 'info');
        return true;
    } else {
        return "No route files found";
    }
};

// Test 10: Check storage link
$tests['Storage Link'] = function() {
    if (is_link('public/storage')) {
        testPrint("Storage link exists", 'info');
        return true;
    } else {
        testPrint("Storage link missing - run 'php artisan storage:link'", 'warning');
        return "Storage link not created";
    }
};

// Test 11: Apple Wallet certificates
$tests['Apple Wallet Certificates'] = function() {
    $certFiles = [
        'certs/tammer.wallet.p12',
        'certs/AppleWWDRCAG3.pem'
    ];
    
    $missing = [];
    foreach ($certFiles as $file) {
        if (!file_exists($file)) {
            $missing[] = $file;
        }
    }
    
    if (empty($missing)) {
        testPrint("Apple Wallet certificates found", 'info');
        return true;
    } else {
        testPrint("Missing certificates: " . implode(', ', $missing), 'warning');
        return "Apple Wallet certificates missing";
    }
};

// Run all tests
foreach ($tests as $testName => $testFunc) {
    if (runTest($testName, $testFunc)) {
        $passed++;
    } else {
        $failed++;
    }
}

// Summary
echo "\n" . colorOutput("📊 Test Summary", 'blue') . "\n";
echo str_repeat('=', 50) . "\n";
echo colorOutput("✅ Passed: $passed", 'green') . "\n";
echo colorOutput("❌ Failed: $failed", 'red') . "\n";

$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo colorOutput("📈 Success Rate: {$percentage}%", $percentage >= 80 ? 'green' : ($percentage >= 60 ? 'yellow' : 'red')) . "\n";

if ($failed === 0) {
    echo "\n" . colorOutput("🎉 All tests passed! Your Laravel installation is working perfectly!", 'green') . "\n";
} elseif ($percentage >= 80) {
    echo "\n" . colorOutput("✅ Installation mostly successful! Check failed tests above.", 'yellow') . "\n";
} else {
    echo "\n" . colorOutput("⚠️ Installation needs attention. Please fix the failed tests.", 'red') . "\n";
}

// Recommendations
echo "\n" . colorOutput("💡 Recommendations:", 'blue') . "\n";
echo "• Run 'composer install' if Composer dependencies failed\n";
echo "• Run 'php artisan key:generate' if APP_KEY is missing\n";
echo "• Run 'chmod -R 755 storage bootstrap/cache' for permission issues\n";
echo "• Run 'php artisan storage:link' if storage link is missing\n";
echo "• Check database credentials in .env file\n";
echo "• Ensure Apple Wallet certificates are uploaded to certs/ directory\n";

// Create test report
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_tests' => $total,
    'passed' => $passed,
    'failed' => $failed,
    'success_rate' => $percentage,
    'php_version' => PHP_VERSION,
    'status' => $failed === 0 ? 'success' : ($percentage >= 80 ? 'warning' : 'error')
];

file_put_contents('installation-test-report.json', json_encode($report, JSON_PRETTY_PRINT));
echo "\n📄 Test report saved to: installation-test-report.json\n";

exit($failed > 0 ? 1 : 0);
?>