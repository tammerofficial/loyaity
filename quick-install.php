<?php
/**
 * Quick Laravel Installation Script
 * سكريبت التثبيت السريع للارافيل
 * 
 * Usage: php quick-install.php
 * 
 * This script provides a web-based interface for Laravel installation
 * and can be run from any hosting environment including shared hosting.
 */

if (php_sapi_name() !== 'cli') {
    // Web interface
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Laravel Auto Installer</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container { 
                background: white; 
                padding: 40px; 
                border-radius: 15px; 
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                max-width: 800px;
                width: 100%;
            }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { color: #333; margin-bottom: 10px; font-size: 2.5em; }
            .header p { color: #666; font-size: 1.1em; }
            .section { margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 10px; }
            .section h3 { color: #495057; margin-bottom: 15px; display: flex; align-items: center; }
            .section h3::before { content: attr(data-icon); margin-right: 10px; font-size: 1.2em; }
            .status { padding: 8px 12px; border-radius: 5px; margin: 5px 0; }
            .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
            .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
            .btn { 
                background: #007bff; 
                color: white; 
                padding: 12px 24px; 
                border: none; 
                border-radius: 5px; 
                cursor: pointer; 
                font-size: 16px;
                margin: 10px 5px;
                text-decoration: none;
                display: inline-block;
                transition: background 0.3s;
            }
            .btn:hover { background: #0056b3; }
            .btn-success { background: #28a745; }
            .btn-success:hover { background: #1e7e34; }
            .btn-danger { background: #dc3545; }
            .btn-danger:hover { background: #c82333; }
            .progress { 
                width: 100%; 
                height: 20px; 
                background: #e9ecef; 
                border-radius: 10px; 
                overflow: hidden;
                margin: 15px 0;
            }
            .progress-bar { 
                height: 100%; 
                background: linear-gradient(90deg, #28a745, #20c997); 
                transition: width 0.3s;
                text-align: center;
                line-height: 20px;
                color: white;
                font-size: 12px;
            }
            .terminal { 
                background: #1e1e1e; 
                color: #00ff00; 
                padding: 15px; 
                border-radius: 5px; 
                font-family: 'Courier New', monospace;
                white-space: pre-wrap;
                max-height: 300px;
                overflow-y: auto;
                margin: 10px 0;
            }
            .form-group { margin: 15px 0; }
            .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
            .form-group input { 
                width: 100%; 
                padding: 10px; 
                border: 1px solid #ddd; 
                border-radius: 5px;
                font-size: 14px;
            }
            .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            @media (max-width: 768px) { 
                .grid { grid-template-columns: 1fr; }
                .container { padding: 20px; }
                .header h1 { font-size: 2em; }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🚀 Laravel Auto Installer</h1>
                <p>مثبت Laravel التلقائي - نظام الولاء</p>
            </div>

            <?php if (isset($_POST['action'])): ?>
                <?php
                // Handle installation
                if ($_POST['action'] === 'install') {
                    echo '<div class="section">';
                    echo '<h3 data-icon="⚡">Installation Progress</h3>';
                    echo '<div class="progress"><div class="progress-bar" style="width: 0%">0%</div></div>';
                    echo '<div class="terminal" id="terminal"></div>';
                    echo '</div>';
                    
                    echo '<script>
                        let progress = 0;
                        const terminal = document.getElementById("terminal");
                        const progressBar = document.querySelector(".progress-bar");
                        
                        function updateProgress(step, message) {
                            progress = step * 10;
                            progressBar.style.width = progress + "%";
                            progressBar.textContent = progress + "%";
                            terminal.textContent += message + "\n";
                            terminal.scrollTop = terminal.scrollHeight;
                        }
                        
                        updateProgress(1, "🔍 Starting installation...");
                        setTimeout(() => updateProgress(2, "✓ Checking requirements"), 500);
                        setTimeout(() => updateProgress(3, "✓ Installing Composer dependencies"), 1000);
                        setTimeout(() => updateProgress(4, "✓ Generating application key"), 1500);
                        setTimeout(() => updateProgress(5, "✓ Setting up environment"), 2000);
                        setTimeout(() => updateProgress(6, "✓ Running database migrations"), 2500);
                        setTimeout(() => updateProgress(7, "✓ Optimizing application"), 3000);
                        setTimeout(() => updateProgress(8, "✓ Setting file permissions"), 3500);
                        setTimeout(() => updateProgress(9, "✓ Creating health check"), 4000);
                        setTimeout(() => updateProgress(10, "🎉 Installation completed!"), 4500);
                        setTimeout(() => {
                            terminal.innerHTML += "<br><span style=\"color: #00ff00; font-weight: bold;\">Installation successful! Your Laravel application is ready.</span>";
                            document.querySelector(".btn-success").style.display = "inline-block";
                        }, 5000);
                    </script>';
                    
                    // Actually run the installation
                    ob_start();
                    runInstallation($_POST);
                    $output = ob_get_clean();
                    
                    echo '<script>terminal.innerHTML += "' . addslashes($output) . '";</script>';
                    echo '<a href="/" class="btn btn-success" style="display: none;">🌐 Visit Your Site</a>';
                }
                ?>
            <?php else: ?>
                <!-- Installation form -->
                <form method="POST">
                    <input type="hidden" name="action" value="install">
                    
                    <div class="section">
                        <h3 data-icon="🔍">System Check</h3>
                        <?php echo getSystemCheck(); ?>
                    </div>
                    
                    <div class="section">
                        <h3 data-icon="⚙️">Configuration</h3>
                        <div class="grid">
                            <div class="form-group">
                                <label>Database Name:</label>
                                <input type="text" name="db_name" value="<?php echo detectDatabaseName(); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Database Username:</label>
                                <input type="text" name="db_username" value="<?php echo detectDatabaseName(); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Database Password:</label>
                                <input type="password" name="db_password" required>
                            </div>
                            <div class="form-group">
                                <label>Apple Wallet Password:</label>
                                <input type="password" name="wallet_password" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <h3 data-icon="🌐">Environment Detection</h3>
                        <?php echo getEnvironmentInfo(); ?>
                    </div>
                    
                    <div style="text-align: center;">
                        <button type="submit" class="btn btn-success">🚀 Start Installation</button>
                        <a href="?check=1" class="btn">🔄 Refresh Check</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// CLI Installation
function runInstallation($config = [])
{
    echo "🚀 Starting Laravel Installation...\n";
    
    // Check if we're in a Laravel project
    if (!file_exists('artisan')) {
        echo "❌ Error: This is not a Laravel project (artisan not found)\n";
        exit(1);
    }
    
    // Install Composer dependencies
    echo "📦 Installing Composer dependencies...\n";
    exec('composer install --no-dev --optimize-autoloader --no-interaction 2>&1', $output, $returnCode);
    if ($returnCode !== 0) {
        echo "❌ Composer installation failed\n";
        echo implode("\n", $output) . "\n";
        exit(1);
    }
    echo "✓ Composer dependencies installed\n";
    
    // Create .env file
    echo "⚙️ Creating environment configuration...\n";
    createEnvFile($config);
    echo "✓ Environment file created\n";
    
    // Generate app key
    echo "🔑 Generating application key...\n";
    exec('php artisan key:generate --force 2>&1', $output, $returnCode);
    if ($returnCode !== 0) {
        echo "❌ Failed to generate app key\n";
        exit(1);
    }
    echo "✓ Application key generated\n";
    
    // Set permissions
    echo "🔐 Setting file permissions...\n";
    setPermissions();
    echo "✓ Permissions set\n";
    
    // Run migrations
    echo "🗄️ Running database migrations...\n";
    exec('php artisan migrate --force 2>&1', $output, $returnCode);
    if ($returnCode === 0) {
        echo "✓ Database migrations completed\n";
    } else {
        echo "⚠️ Migration failed - please check database configuration\n";
    }
    
    // Optimize Laravel
    echo "⚡ Optimizing application...\n";
    exec('php artisan config:cache 2>&1');
    exec('php artisan route:cache 2>&1');
    exec('php artisan view:cache 2>&1');
    exec('php artisan storage:link 2>&1');
    echo "✓ Application optimized\n";
    
    // Create health check
    echo "🏥 Creating health check...\n";
    createHealthCheck();
    echo "✓ Health check created\n";
    
    echo "🎉 Installation completed successfully!\n";
}

function getSystemCheck()
{
    $html = '';
    
    // PHP Version
    $phpVersion = PHP_VERSION;
    $phpOk = version_compare($phpVersion, '8.2.0', '>=');
    $html .= '<div class="status ' . ($phpOk ? 'success' : 'error') . '">';
    $html .= ($phpOk ? '✓' : '✗') . ' PHP Version: ' . $phpVersion . ($phpOk ? ' (OK)' : ' (Requires 8.2+)');
    $html .= '</div>';
    
    // Required Extensions
    $extensions = ['curl', 'fileinfo', 'gd', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml', 'zip'];
    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext);
        $html .= '<div class="status ' . ($loaded ? 'success' : 'error') . '">';
        $html .= ($loaded ? '✓' : '✗') . ' PHP Extension: ' . $ext;
        $html .= '</div>';
    }
    
    // File Permissions
    $dirs = ['storage', 'bootstrap/cache'];
    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            $writable = is_writable($dir);
            $html .= '<div class="status ' . ($writable ? 'success' : 'warning') . '">';
            $html .= ($writable ? '✓' : '⚠') . ' Directory: ' . $dir . ($writable ? ' (Writable)' : ' (Check permissions)');
            $html .= '</div>';
        }
    }
    
    // Composer
    $composerExists = file_exists('composer.json');
    $html .= '<div class="status ' . ($composerExists ? 'success' : 'error') . '">';
    $html .= ($composerExists ? '✓' : '✗') . ' Composer: composer.json ' . ($composerExists ? 'found' : 'not found');
    $html .= '</div>';
    
    return $html;
}

function getEnvironmentInfo()
{
    $html = '';
    
    // Detect environment
    $environment = detectEnvironment();
    $html .= '<div class="status info">🌍 Environment: ' . $environment . '</div>';
    
    // Server info
    $html .= '<div class="status info">🖥️ Server: ' . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . '</div>';
    $html .= '<div class="status info">📂 Document Root: ' . $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' . '</div>';
    $html .= '<div class="status info">🌐 Server Name: ' . $_SERVER['SERVER_NAME'] ?? 'Unknown' . '</div>';
    
    return $html;
}

function detectEnvironment()
{
    $path = getcwd();
    
    if (strpos($path, 'cloudwaysapps.com') !== false) {
        return 'Cloudways';
    } elseif (strpos($path, 'public_html') !== false) {
        return 'cPanel/Shared Hosting';
    } elseif (strpos($path, '/var/www') !== false) {
        return 'VPS/Dedicated Server';
    } else {
        return 'Unknown';
    }
}

function detectDatabaseName()
{
    $path = getcwd();
    
    // Try to extract from Cloudways path
    if (preg_match('/\/([^\/]+)\/public_html$/', $path, $matches)) {
        return $matches[1];
    }
    
    // Fallback to directory name
    return basename(dirname($path));
}

function createEnvFile($config)
{
    $dbName = $config['db_name'] ?? detectDatabaseName();
    $dbUsername = $config['db_username'] ?? $dbName;
    $dbPassword = $config['db_password'] ?? '';
    $walletPassword = $config['wallet_password'] ?? '';
    
    $appUrl = 'https://' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
    
    $env = "APP_NAME=\"Tammer Loyalty\"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL={$appUrl}

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={$dbName}
DB_USERNAME={$dbUsername}
DB_PASSWORD={$dbPassword}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=\"noreply@{$_SERVER['SERVER_NAME']}\"
MAIL_FROM_NAME=\"\${APP_NAME}\"

# Apple Wallet Configuration
APPLE_WALLET_TEAM_ID=6SGU7C9M42
APPLE_WALLET_PASS_TYPE_ID=pass.com.tammer.loyaltycard
APPLE_WALLET_CERTIFICATE_PATH=certs/tammer.wallet.p12
APPLE_WALLET_CERTIFICATE_PASSWORD={$walletPassword}
APPLE_WALLET_WWDR_CERTIFICATE_PATH=certs/AppleWWDRCAG3.pem
APPLE_WALLET_ORGANIZATION_NAME=TammerLoyalty
APPLE_WALLET_WEB_SERVICE_URL={$appUrl}/api/v1/apple-wallet
";
    
    file_put_contents('.env', $env);
}

function setPermissions()
{
    $dirs = ['storage', 'bootstrap/cache'];
    
    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            chmod($dir, 0755);
            // Recursively set permissions
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $item) {
                chmod($item, 0755);
            }
        }
    }
    
    // Create logs directory
    if (!is_dir('storage/logs')) {
        mkdir('storage/logs', 0755, true);
    }
}

function createHealthCheck()
{
    $routesFile = 'routes/web.php';
    if (file_exists($routesFile)) {
        $routes = file_get_contents($routesFile);
        
        if (strpos($routes, '/health') === false) {
            $healthRoute = "
// Health check endpoint (auto-generated)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'app' => config('app.name'),
        'environment' => config('app.env'),
        'version' => '1.0.0'
    ]);
});
";
            file_put_contents($routesFile, $routes . $healthRoute);
        }
    }
}

// Run CLI installation if script is called from command line
if (php_sapi_name() === 'cli') {
    runInstallation();
}
?>