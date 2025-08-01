#!/bin/bash

echo "🔧 Fixing Forbidden Error on Cloudways..."

# Server details
SERVER_IP="108.61.99.171"
SERVER_USER="alalawi310"
SERVER_PASS="Ali@kuwait@90"

echo "📡 Connecting to server..."
sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no $SERVER_USER@$SERVER_IP << 'EOF'

echo "📍 Navigating to application directory..."
cd /home/master/applications/uzwgphvuyb/public_html

echo "🔍 Checking current directory..."
pwd
ls -la

echo "📝 Creating proper .htaccess file..."
cat > public/.htaccess << 'HTACCESS'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable directory browsing
Options -Indexes

# Protect against access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>
HTACCESS

echo "🔐 Setting proper permissions..."
chmod 644 public/.htaccess
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "⚙️ Updating .env file..."
cat > .env << 'ENVEOF'
APP_NAME="Tammer Loyalty"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://phpstack-1446204-5743070.cloudwaysapps.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uzwgphvuyb
DB_USERNAME=uzwgphvuyb
DB_PASSWORD=krk92QXrsz

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Apple Wallet Settings
APPLE_WALLET_TEAM_ID=6SGU7C9M42
APPLE_WALLET_PASS_TYPE_ID=pass.com.tammer.loyaltycard
APPLE_WALLET_CERTIFICATE_PATH=certs/tammer.wallet.p12
APPLE_WALLET_CERTIFICATE_PASSWORD=your_password
APPLE_WALLET_WWDR_CERTIFICATE_PATH=certs/AppleWWDRCAG3.pem
APPLE_WALLET_ORGANIZATION_NAME=Tammer Loyalty
APPLE_WALLET_WEB_SERVICE_URL=https://phpstack-1446204-5743070.cloudwaysapps.com/api/v1/apple-wallet
ENVEOF

echo "🔑 Generating APP_KEY..."
php artisan key:generate

echo "🧹 Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "🏗️ Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🗄️ Running migrations..."
php artisan migrate --force

echo "🔗 Creating storage symlink..."
php artisan storage:link

echo "🔐 Setting final permissions..."
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

echo "✅ Fix completed!"
echo "🌐 Testing the site..."
curl -I https://phpstack-1446204-5743070.cloudwaysapps.com

EOF

echo "🎉 Forbidden error fix completed!" 