<?php
/**
 * Update Composer Scripts for Auto Installation
 * تحديث سكريبت Composer للتثبيت التلقائي
 */

$composerFile = 'composer.json';
$backupFile = 'composer.json.backup.' . date('Y-m-d-H-i-s');

if (!file_exists($composerFile)) {
    echo "❌ composer.json not found!\n";
    exit(1);
}

// Backup original composer.json
copy($composerFile, $backupFile);
echo "📋 Created backup: $backupFile\n";

// Load composer.json
$composer = json_decode(file_get_contents($composerFile), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Invalid JSON in composer.json\n";
    exit(1);
}

// Add auto-installation scripts
$autoScripts = [
    "post-install-cmd" => [
        "@php -r \"if (!file_exists('.env')) { copy('.env.example', '.env'); echo '.env file created from .env.example\\n'; }\"",
        "@php artisan key:generate --ansi --force",
        "@php -r \"if (!is_dir('storage/logs')) { mkdir('storage/logs', 0755, true); echo 'Created storage/logs directory\\n'; }\"",
        "@php -r \"if (!is_dir('bootstrap/cache')) { mkdir('bootstrap/cache', 0755, true); echo 'Created bootstrap/cache directory\\n'; }\"",
        "@php -r \"if (DIRECTORY_SEPARATOR === '/') { exec('chmod -R 755 storage'); exec('chmod -R 755 bootstrap/cache'); echo 'Permissions set for Unix/Linux\\n'; } else { echo 'Windows detected - permissions not set\\n'; }\"",
        "@php artisan storage:link",
        "@php artisan config:cache",
        "@php artisan route:cache",
        "@php artisan view:cache",
        "echo 'Laravel auto-installation completed successfully! 🎉'"
    ],
    "post-update-cmd" => [
        "@php artisan vendor:publish --tag=laravel-assets --ansi --force",
        "@php artisan migrate --force",
        "@php artisan config:cache",
        "@php artisan route:cache", 
        "@php artisan view:cache",
        "@php -r \"if (DIRECTORY_SEPARATOR === '/') { exec('chmod -R 755 storage'); exec('chmod -R 755 bootstrap/cache'); echo 'Permissions updated\\n'; }\"",
        "echo 'Laravel update completed successfully! ✅'"
    ],
    "post-autoload-dump" => [
        "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
        "@php artisan package:discover --ansi"
    ],
    // Custom deployment script
    "deploy" => [
        "@php artisan down --retry=60",
        "@php artisan config:clear",
        "@php artisan cache:clear", 
        "@php artisan route:clear",
        "@php artisan view:clear",
        "@php artisan migrate --force",
        "@php artisan config:cache",
        "@php artisan route:cache",
        "@php artisan view:cache",
        "@php artisan up",
        "echo 'Deployment completed! 🚀'"
    ],
    // Development helpers
    "fresh" => [
        "@php artisan migrate:fresh --seed",
        "@php artisan config:cache",
        "@php artisan route:cache",
        "@php artisan view:cache",
        "echo 'Fresh installation with seed data completed! 🌱'"
    ],
    "optimize" => [
        "@php artisan config:cache",
        "@php artisan route:cache",
        "@php artisan view:cache",
        "@php artisan storage:link",
        "echo 'Application optimized! ⚡'"
    ],
    "clear-all" => [
        "@php artisan config:clear",
        "@php artisan cache:clear",
        "@php artisan route:clear", 
        "@php artisan view:clear",
        "@php artisan queue:clear",
        "echo 'All caches cleared! 🧹'"
    ]
];

// Merge with existing scripts
if (!isset($composer['scripts'])) {
    $composer['scripts'] = [];
}

foreach ($autoScripts as $scriptName => $commands) {
    if (isset($composer['scripts'][$scriptName])) {
        echo "⚠️  Script '$scriptName' already exists, merging...\n";
        
        // Merge unique commands
        $existing = (array) $composer['scripts'][$scriptName];
        $merged = array_unique(array_merge($existing, $commands));
        $composer['scripts'][$scriptName] = array_values($merged);
    } else {
        echo "✅ Adding script '$scriptName'\n";
        $composer['scripts'][$scriptName] = $commands;
    }
}

// Add custom installer commands
$composer['scripts']['install-fresh'] = [
    "Composer\\Config::disableProcessTimeout",
    "@clear-all",
    "@post-install-cmd",
    "@fresh"
];

$composer['scripts']['install-production'] = [
    "Composer\\Config::disableProcessTimeout", 
    "@post-install-cmd",
    "@php artisan migrate --force",
    "@optimize"
];

// Update the file
$jsonOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
$updatedJson = json_encode($composer, $jsonOptions);

if (file_put_contents($composerFile, $updatedJson)) {
    echo "✅ composer.json updated successfully!\n";
    echo "\n📋 Available commands:\n";
    echo "  composer install-fresh      - Fresh installation with seed data\n";
    echo "  composer install-production - Production installation\n";
    echo "  composer deploy             - Deploy application\n";
    echo "  composer optimize           - Optimize application\n";
    echo "  composer clear-all          - Clear all caches\n";
    echo "  composer fresh              - Fresh database with seeds\n";
    echo "\n🎯 Quick start commands:\n";
    echo "  composer install            - Install with auto-setup\n";
    echo "  composer update             - Update with optimization\n";
} else {
    echo "❌ Failed to update composer.json\n";
    echo "Restoring backup...\n";
    copy($backupFile, $composerFile);
    exit(1);
}

echo "\n🎉 Composer auto-installation scripts added successfully!\n";
echo "💡 Run 'composer install' to trigger auto-installation.\n";
?>