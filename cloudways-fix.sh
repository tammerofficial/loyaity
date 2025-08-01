#!/bin/bash

# 🚨 حل سريع لمشكلة Cloudways - نسخ ولصق هذه الأوامر

echo "🔧 إصلاح Laravel على Cloudways..."

# الاتصال بالسيرفر
echo "ssh alalawi@108.61.99.171"
echo "كلمة المرور: Ali@kuwait@90"
echo ""

# الانتقال للمجلد الصحيح
echo "cd /home/1446204.cloudwaysapps.com/kbbkehepay/public_html"
echo ""

# فحص الملفات الموجودة
echo "ls -la"
echo ""

# إنشاء ملف .env
echo "إنشاء ملف .env:"
echo "======================================"
cat << 'EOF'
cat > .env << 'ENVEOF'
APP_NAME="Tammer Loyalty"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://phplaravel-1446204-5743154.cloudwaysapps.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kbbkehepay
DB_USERNAME=kbbkehepay
DB_PASSWORD=htRxYRa6XN

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

APPLE_WALLET_TEAM_ID=6SGU7C9M42
APPLE_WALLET_PASS_TYPE_ID=pass.com.tammer.loyaltycard
APPLE_WALLET_CERTIFICATE_PATH=certs/tammer.wallet.p12
APPLE_WALLET_CERTIFICATE_PASSWORD=
APPLE_WALLET_WWDR_CERTIFICATE_PATH=certs/AppleWWDRCAG3.pem
APPLE_WALLET_ORGANIZATION_NAME=TammerLoyalty
APPLE_WALLET_WEB_SERVICE_URL=https://phplaravel-1446204-5743154.cloudwaysapps.com/api/v1/apple-wallet
ENVEOF
EOF

echo ""
echo "الأوامر الأساسية:"
echo "=================="
echo "php artisan key:generate --force"
echo "composer install --no-dev --optimize-autoloader --no-interaction"
echo "chmod -R 755 storage"
echo "chmod -R 755 bootstrap/cache"
echo "php artisan migrate --force"
echo "php artisan config:cache"
echo "php artisan route:cache"
echo "php artisan view:cache"
echo ""

echo "اختبار الموقع:"
echo "=============="
echo "https://phplaravel-1446204-5743154.cloudwaysapps.com/health"