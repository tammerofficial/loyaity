# 🚀 Cloudways Deployment Guide

## 📋 Prerequisites

1. **Install sshpass** (for automated SSH):
```bash
# On macOS
brew install sshpass

# On Ubuntu/Debian
sudo apt-get install sshpass
```

## 🔧 Quick Fix for Forbidden Error

### Method 1: Automated Fix
```bash
./fix-forbidden.sh
```

### Method 2: Manual Fix

#### Step 1: Connect to Server
```bash
ssh alalawi310@108.61.99.171
```

#### Step 2: Navigate to Application
```bash
cd /home/master/applications/uzwgphvuyb/public_html
```

#### Step 3: Create Proper .htaccess
```bash
cat > public/.htaccess << 'EOF'
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
EOF
```

#### Step 4: Set Permissions
```bash
chmod 644 public/.htaccess
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

#### Step 5: Update .env File
```bash
cat > .env << 'EOF'
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
EOF
```

#### Step 6: Laravel Commands
```bash
# Generate APP_KEY
php artisan key:generate

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Create storage symlink
php artisan storage:link

# Set final permissions
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

## 🚀 Full Deployment

### Method 1: Automated Deployment
```bash
./deploy.sh
```

### Method 2: Manual Deployment

#### Step 1: Create Deployment Package
```bash
tar -czf deployment.tar.gz \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='.env' \
    .
```

#### Step 2: Upload to Server
```bash
sshpass -p "Ali@kuwait@90" scp -o StrictHostKeyChecking=no deployment.tar.gz alalawi310@108.61.99.171:/tmp/
```

#### Step 3: Extract and Setup
```bash
ssh alalawi310@108.61.99.171
cd /home/master/applications/uzwgphvuyb/public_html
tar -xzf /tmp/deployment.tar.gz
```

Then follow the manual fix steps above.

## 🔍 Troubleshooting

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Check Permissions
```bash
ls -la storage/
ls -la bootstrap/cache/
ls -la public/.htaccess
```

### Test Site
```bash
curl -I https://phpstack-1446204-5743070.cloudwaysapps.com
```

### Common Issues

1. **Forbidden Error**: Usually caused by incorrect .htaccess or permissions
2. **500 Error**: Check Laravel logs and APP_KEY
3. **Database Connection**: Verify database credentials in .env
4. **File Permissions**: Ensure storage and cache directories are writable

## 📱 Apple Wallet Configuration

The application is configured to use:
- **Web Service URL**: `https://phpstack-1446204-5743070.cloudwaysapps.com/api/v1/apple-wallet`
- **Team ID**: `6SGU7C9M42`
- **Pass Type ID**: `pass.com.tammer.loyaltycard`

Make sure to:
1. Upload certificate files to `storage/certs/`
2. Update certificate passwords in `.env`
3. Test Apple Wallet integration

## 🌐 Final URL

Your application will be available at:
**https://phpstack-1446204-5743070.cloudwaysapps.com**

## 📞 Support

If you encounter any issues:
1. Check the logs: `tail -f storage/logs/laravel.log`
2. Verify file permissions
3. Ensure all Laravel commands completed successfully
4. Test the site with curl before accessing via browser 