#!/bin/bash

# Deployment Script for Cloudways
echo "🚀 Starting deployment to Cloudways..."

# Server details
SERVER_IP="108.61.99.171"
SERVER_USER="alalawi310"
SERVER_PASS="Ali@kuwait@90"
APP_PATH="/home/master/applications/uzwgphvuyb/public_html"

# Create deployment package
echo "📦 Creating deployment package..."
tar -czf deployment.tar.gz \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='.env' \
    .

# Upload to server
echo "📤 Uploading to server..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no deployment.tar.gz $SERVER_USER@$SERVER_IP:/tmp/

# Execute deployment commands
echo "🔧 Executing deployment commands..."
sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no $SERVER_USER@$SERVER_IP << 'EOF'

cd /home/master/applications/uzwgphvuyb/public_html

# Backup current files
echo "💾 Creating backup..."
tar -czf backup_$(date +%Y%m%d_%H%M%S).tar.gz .

# Extract new files
echo "📂 Extracting new files..."
tar -xzf /tmp/deployment.tar.gz

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 public/.htaccess

# Update .env file
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

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Generate APP_KEY
echo "🔑 Generating APP_KEY..."
php artisan key:generate

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
echo "🏗️ Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link

# Set final permissions
echo "🔐 Setting final permissions..."
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

echo "✅ Deployment completed successfully!"
echo "🌐 Your site should be available at: https://phpstack-1446204-5743070.cloudwaysapps.com"

EOF

# Clean up
echo "🧹 Cleaning up..."
rm deployment.tar.gz

echo "🎉 Deployment script completed!" 