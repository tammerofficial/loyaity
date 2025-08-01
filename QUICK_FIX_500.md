# 🚨 Quick Fix for 500 Error

## 🔧 Manual Fix Steps

### Step 1: Connect to Server
```bash
ssh alalawi@108.61.99.171
```

### Step 2: Navigate to Application
```bash
cd /home/1446204.cloudwaysapps.com/kbbkehepay/public_html
```

### Step 3: Fix .env File
```bash
cat > .env << 'EOF'
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

# Apple Wallet Settings
APPLE_WALLET_TEAM_ID=6SGU7C9M42
APPLE_WALLET_PASS_TYPE_ID=pass.com.tammer.loyaltycard
APPLE_WALLET_CERTIFICATE_PATH=certs/tammer.wallet.p12
APPLE_WALLET_CERTIFICATE_PASSWORD=your_password
APPLE_WALLET_WWDR_CERTIFICATE_PATH=certs/AppleWWDRCAG3.pem
APPLE_WALLET_ORGANIZATION_NAME=TammerLoyalty
APPLE_WALLET_WEB_SERVICE_URL=https://phplaravel-1446204-5743154.cloudwaysapps.com/api/v1/apple-wallet
EOF
```

### Step 4: Generate APP_KEY
```bash
php artisan key:generate
```

### Step 5: Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 6: Rebuild Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 7: Run Migrations
```bash
php artisan migrate --force
```

### Step 8: Set Permissions
```bash
chmod 644 public/.htaccess
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Step 9: Create Storage Link
```bash
php artisan storage:link
```

### Step 10: Test the Site
```bash
curl -I https://phplaravel-1446204-5743154.cloudwaysapps.com
```

## 🔍 Troubleshooting

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Check Database Connection
```bash
php artisan tinker --execute="echo 'DB Connection: ' . (DB::connection()->getPdo() ? 'OK' : 'FAILED');"
```

### Check File Permissions
```bash
ls -la storage/
ls -la bootstrap/cache/
ls -la public/.htaccess
```

## 🌐 Final URL
**https://phplaravel-1446204-5743154.cloudwaysapps.com**

## 📞 If Still Having Issues
1. Check if all files are uploaded correctly
2. Verify database credentials
3. Ensure APP_KEY is generated
4. Check storage and cache permissions
5. Review Laravel logs for specific errors 