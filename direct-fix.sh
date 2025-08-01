#!/bin/bash
# Direct Fix Script for 500 Error
# Copy each command one by one and execute on server

echo "🚨 DIRECT FIX FOR 500 ERROR"
echo "Copy and execute these commands on the server:"
echo ""

echo "# 1. Connect to server"
echo "ssh alalawi@108.61.99.171"
echo ""

echo "# 2. Navigate to app directory"
echo "cd /home/1446204.cloudwaysapps.com/kbbkehepay/public_html"
echo ""

echo "# 3. Fix .env file (copy this entire block)"
echo 'cat > .env << '"'"'EOF'"'"'
APP_NAME="Tammer Loyalty"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://phplaravel-1446204-5743154.cloudwaysapps.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kbbkehepay
DB_USERNAME=kbbkehepay
DB_PASSWORD=htRxYRa6XN

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

APPLE_WALLET_TEAM_ID=6SGU7C9M42
APPLE_WALLET_PASS_TYPE_ID=pass.com.tammer.loyaltycard
APPLE_WALLET_CERTIFICATE_PATH=certs/tammer.wallet.p12
APPLE_WALLET_CERTIFICATE_PASSWORD=your_password
APPLE_WALLET_WWDR_CERTIFICATE_PATH=certs/AppleWWDRCAG3.pem
APPLE_WALLET_ORGANIZATION_NAME=TammerLoyalty
APPLE_WALLET_WEB_SERVICE_URL=https://phplaravel-1446204-5743154.cloudwaysapps.com/api/v1/apple-wallet
EOF'
echo ""

echo "# 4. Generate APP_KEY"
echo "php artisan key:generate --force"
echo ""

echo "# 5. Clear all caches"
echo "php artisan config:clear"
echo "php artisan cache:clear"
echo "php artisan route:clear"
echo "php artisan view:clear"
echo ""

echo "# 6. Rebuild caches"
echo "php artisan config:cache"
echo "php artisan route:cache"
echo "php artisan view:cache"
echo ""

echo "# 7. Set permissions"
echo "chmod -R 755 storage"
echo "chmod -R 755 bootstrap/cache"
echo "chmod 644 public/.htaccess"
echo ""

echo "# 8. Database setup"
echo "php artisan migrate --force"
echo "php artisan storage:link"
echo ""

echo "# 9. Test the site"
echo "curl -I https://phplaravel-1446204-5743154.cloudwaysapps.com"
echo ""

echo "🌐 Target URL: https://phplaravel-1446204-5743154.cloudwaysapps.com"