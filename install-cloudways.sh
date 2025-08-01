#!/bin/bash

# 🌤️ Cloudways Specific Installation Script
# نظام التثبيت المخصص لـ Cloudways
# Auto-detects Cloudways environment and applies specific configurations

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Cloudways detection and configuration
detect_cloudways_config() {
    print_status "🔍 Detecting Cloudways configuration..."
    
    # Extract database info from current path
    CURRENT_PATH=$(pwd)
    
    if [[ $CURRENT_PATH =~ /home/([0-9]+\.cloudwaysapps\.com)/([^/]+)/public_html ]]; then
        CLOUDWAYS_DOMAIN="${BASH_REMATCH[1]}"
        APP_NAME="${BASH_REMATCH[2]}"
        
        # Extract application ID from domain
        APP_ID=$(echo $CLOUDWAYS_DOMAIN | cut -d'.' -f1)
        
        print_success "Detected Cloudways App: $APP_NAME"
        print_success "Application ID: $APP_ID"
        print_success "Domain: $CLOUDWAYS_DOMAIN"
        
        # Set database configuration
        DB_NAME="$APP_NAME"
        DB_USERNAME="$APP_NAME"
        APP_URL="https://phplaravel-$APP_ID-$(date +%s | tail -c 8).cloudwaysapps.com"
        
        return 0
    else
        print_error "Could not detect Cloudways configuration from path: $CURRENT_PATH"
        return 1
    fi
}

# Function to create optimized .env for Cloudways
create_cloudways_env() {
    print_status "⚙️ Creating Cloudways-optimized .env file..."
    
    # Backup existing .env
    if [ -f ".env" ]; then
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
        print_warning "Existing .env backed up"
    fi
    
    # Prompt for database password if not provided
    if [ -z "$DB_PASSWORD" ]; then
        echo -n "Enter database password for $DB_USERNAME: "
        read -s DB_PASSWORD
        echo
    fi
    
    # Prompt for Apple Wallet password
    echo -n "Enter Apple Wallet certificate password (or press Enter to skip): "
    read -s WALLET_PASSWORD
    echo
    
cat > .env << EOF
APP_NAME="Tammer Loyalty"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=$APP_URL

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USERNAME
DB_PASSWORD=$DB_PASSWORD

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
MAIL_FROM_ADDRESS="noreply@${APP_URL#https://}"
MAIL_FROM_NAME="\${APP_NAME}"

# Apple Wallet Configuration
APPLE_WALLET_TEAM_ID=6SGU7C9M42
APPLE_WALLET_PASS_TYPE_ID=pass.com.tammer.loyaltycard
APPLE_WALLET_CERTIFICATE_PATH=certs/tammer.wallet.p12
APPLE_WALLET_CERTIFICATE_PASSWORD=$WALLET_PASSWORD
APPLE_WALLET_WWDR_CERTIFICATE_PATH=certs/AppleWWDRCAG3.pem
APPLE_WALLET_ORGANIZATION_NAME=TammerLoyalty
APPLE_WALLET_WEB_SERVICE_URL=$APP_URL/api/v1/apple-wallet

# Cloudways Optimizations
CACHE_PREFIX="${APP_NAME}_cache"
SESSION_COOKIE="${APP_NAME}_session"
REDIS_PREFIX="${APP_NAME}:"
EOF
    
    print_success ".env file created with Cloudways configuration"
}

# Cloudways-specific optimizations
apply_cloudways_optimizations() {
    print_status "⚡ Applying Cloudways optimizations..."
    
    # Create optimized PHP configuration
    if [ ! -f ".user.ini" ]; then
        cat > .user.ini << EOF
; Cloudways PHP Optimizations
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
max_input_vars = 3000

; OPcache settings
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
EOF
        print_success "Created .user.ini with optimized PHP settings"
    fi
    
    # Create optimized .htaccess for Cloudways
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
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    <FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg)$">
        SetOutputFilter DEFLATE
    </FilesMatch>
</IfModule>

# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/ico "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
EOF
    
    print_success "Optimized .htaccess created"
}

# Test Cloudways-specific functionality
test_cloudways_setup() {
    print_status "🧪 Testing Cloudways setup..."
    
    # Test database connection
    if php artisan migrate:status >/dev/null 2>&1; then
        print_success "✓ Database connection successful"
    else
        print_warning "⚠ Database connection failed - check credentials"
    fi
    
    # Test file permissions
    if [ -w "storage/logs" ] && [ -w "bootstrap/cache" ]; then
        print_success "✓ File permissions correct"
    else
        print_warning "⚠ File permission issues detected"
    fi
    
    # Test PHP configuration
    php -r "
    if (ini_get('memory_limit') !== '256M') {
        echo 'Warning: Memory limit not optimized\n';
    }
    if (!extension_loaded('gd')) {
        echo 'Warning: GD extension not loaded\n';
    }
    if (!extension_loaded('curl')) {
        echo 'Warning: cURL extension not loaded\n';
    }
    echo 'PHP configuration check complete\n';
    "
}

# Create Cloudways-specific deployment script
create_deployment_script() {
    print_status "📦 Creating deployment script..."
    
    cat > deploy-cloudways.sh << 'EOF'
#!/bin/bash
# Cloudways Deployment Script

echo "🚀 Deploying to Cloudways..."

# Pull latest changes
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Clear and rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "✅ Deployment complete!"
EOF
    
    chmod +x deploy-cloudways.sh
    print_success "Deployment script created: deploy-cloudways.sh"
}

# Main Cloudways installation
main() {
    echo -e "${BLUE}"
    echo "=================================="
    echo "  Cloudways Laravel Installer"
    echo "=================================="
    echo -e "${NC}"
    
    if [ ! -f "artisan" ]; then
        print_error "This is not a Laravel project!"
        exit 1
    fi
    
    if ! detect_cloudways_config; then
        print_error "Failed to detect Cloudways configuration"
        exit 1
    fi
    
    # Install dependencies
    print_status "📚 Installing dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
    
    create_cloudways_env
    
    # Generate app key
    print_status "🔑 Generating application key..."
    php artisan key:generate --force
    
    # Set permissions
    print_status "🔐 Setting permissions..."
    chmod -R 755 storage
    chmod -R 755 bootstrap/cache
    mkdir -p storage/logs
    chmod -R 755 storage/logs
    
    apply_cloudways_optimizations
    
    # Laravel optimizations
    print_status "⚡ Optimizing Laravel..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan storage:link
    
    # Run migrations
    print_status "🗄️ Running migrations..."
    php artisan migrate --force || print_warning "Migration failed - check database credentials"
    
    create_deployment_script
    test_cloudways_setup
    
    echo -e "${GREEN}"
    echo "🎉 Cloudways installation complete!"
    echo ""
    echo "Next steps:"
    echo "1. Verify database credentials in .env"
    echo "2. Update Apple Wallet password if needed"
    echo "3. Test your application: $APP_URL"
    echo "4. Use ./deploy-cloudways.sh for future deployments"
    echo -e "${NC}"
}

main "$@"
EOF