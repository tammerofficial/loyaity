#!/bin/bash

# 🚀 Laravel Auto Install & Deploy System
# نظام التثبيت التلقائي للارافيل
# Created by: Assistant
# Version: 1.0

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="Tammer Loyalty System"
APP_ENV="production"
LOG_LEVEL="error"

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}"
    echo "=================================="
    echo "  $1"
    echo "=================================="
    echo -e "${NC}"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to detect server environment
detect_environment() {
    print_header "🔍 Detecting Server Environment"
    
    # Check for Cloudways
    if [ -d "/home/master/applications" ] || [ -d "/home" ] && ls /home/ | grep -E "^[0-9]+\.cloudwaysapps\.com$" >/dev/null 2>&1; then
        ENVIRONMENT="cloudways"
        print_success "Detected: Cloudways Environment"
    # Check for cPanel
    elif [ -d "/home" ] && ls /home/ | grep -E "public_html" >/dev/null 2>&1; then
        ENVIRONMENT="cpanel"
        print_success "Detected: cPanel Environment"
    # Check for VPS/Dedicated
    elif [ -d "/var/www" ]; then
        ENVIRONMENT="vps"
        print_success "Detected: VPS/Dedicated Server"
    else
        ENVIRONMENT="unknown"
        print_warning "Unknown environment, using generic setup"
    fi
}

# Function to check PHP version and extensions
check_php_requirements() {
    print_header "🔧 Checking PHP Requirements"
    
    if ! command_exists php; then
        print_error "PHP is not installed!"
        exit 1
    fi
    
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_status "PHP Version: $PHP_VERSION"
    
    # Check minimum PHP version (8.2)
    if php -r "exit(version_compare(PHP_VERSION, '8.2.0', '<') ? 1 : 0);"; then
        print_error "PHP 8.2+ is required. Current version: $PHP_VERSION"
        exit 1
    fi
    
    # Check required extensions
    REQUIRED_EXTENSIONS=("curl" "fileinfo" "gd" "mbstring" "openssl" "pdo" "tokenizer" "xml" "zip" "json" "bcmath" "ctype")
    
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if php -m | grep -q "^$ext$"; then
            print_success "✓ $ext extension is loaded"
        else
            print_error "✗ $ext extension is missing"
            MISSING_EXTENSIONS+=("$ext")
        fi
    done
    
    if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
        print_error "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
        print_status "Please install missing extensions and run again"
        exit 1
    fi
}

# Function to check and install Composer
check_composer() {
    print_header "📦 Checking Composer"
    
    if ! command_exists composer; then
        print_warning "Composer not found. Installing..."
        
        # Download and install Composer
        curl -sS https://getcomposer.org/installer | php
        sudo mv composer.phar /usr/local/bin/composer
        sudo chmod +x /usr/local/bin/composer
        
        if command_exists composer; then
            print_success "Composer installed successfully"
        else
            print_error "Failed to install Composer"
            exit 1
        fi
    else
        print_success "Composer is already installed"
        composer --version
    fi
}

# Function to install dependencies
install_dependencies() {
    print_header "📚 Installing Dependencies"
    
    if [ -f "composer.json" ]; then
        print_status "Installing Composer dependencies..."
        composer install --no-dev --optimize-autoloader --no-interaction
        print_success "Composer dependencies installed"
    else
        print_error "composer.json not found!"
        exit 1
    fi
    
    if [ -f "package.json" ]; then
        if command_exists npm; then
            print_status "Installing NPM dependencies..."
            npm ci --production
            npm run build
            print_success "NPM dependencies installed and assets built"
        elif command_exists yarn; then
            print_status "Installing Yarn dependencies..."
            yarn install --production
            yarn build
            print_success "Yarn dependencies installed and assets built"
        else
            print_warning "Neither NPM nor Yarn found. Skipping Node.js dependencies"
        fi
    fi
}

# Function to create .env file
create_env_file() {
    print_header "⚙️ Creating Environment Configuration"
    
    if [ -f ".env" ]; then
        print_warning ".env file exists. Creating backup..."
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    fi
    
    # Detect database from current directory or common patterns
    DB_NAME=""
    CURRENT_DIR=$(basename "$PWD")
    
    # Try to detect database name from directory structure
    if [[ $ENVIRONMENT == "cloudways" ]]; then
        # Extract from path like /home/1446204.cloudwaysapps.com/kbbkehepay/public_html
        if [[ $PWD =~ /([^/]+)/public_html$ ]]; then
            DB_NAME="${BASH_REMATCH[1]}"
        fi
    elif [[ $ENVIRONMENT == "cpanel" ]]; then
        # Use current directory name
        DB_NAME="$CURRENT_DIR"
    fi
    
    # Get server URL
    if [ -n "$SERVER_NAME" ]; then
        APP_URL="https://$SERVER_NAME"
    elif [ -n "$HTTP_HOST" ]; then
        APP_URL="https://$HTTP_HOST"
    else
        APP_URL="https://localhost"
    fi
    
    print_status "Creating .env file..."
    
cat > .env << EOF
APP_NAME="$PROJECT_NAME"
APP_ENV=$APP_ENV
APP_KEY=
APP_DEBUG=false
APP_URL=$APP_URL

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=$LOG_LEVEL

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_NAME
DB_PASSWORD=

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
APPLE_WALLET_CERTIFICATE_PASSWORD=
APPLE_WALLET_WWDR_CERTIFICATE_PATH=certs/AppleWWDRCAG3.pem
APPLE_WALLET_ORGANIZATION_NAME=TammerLoyalty
APPLE_WALLET_WEB_SERVICE_URL=$APP_URL/api/v1/apple-wallet
EOF
    
    print_success ".env file created successfully"
    print_warning "Please update database credentials and Apple Wallet password in .env file"
}

# Function to generate app key
generate_app_key() {
    print_header "🔑 Generating Application Key"
    
    php artisan key:generate --force
    print_success "Application key generated"
}

# Function to set proper permissions
set_permissions() {
    print_header "🔐 Setting File Permissions"
    
    # Laravel required permissions
    chmod -R 755 storage
    chmod -R 755 bootstrap/cache
    
    # Make sure log directory exists and is writable
    mkdir -p storage/logs
    chmod -R 755 storage/logs
    
    # Set proper permissions for config cache
    mkdir -p bootstrap/cache
    chmod -R 755 bootstrap/cache
    
    # Set permissions for public directory
    if [ -f "public/.htaccess" ]; then
        chmod 644 public/.htaccess
    fi
    
    # Set ownership if running as root
    if [ "$EUID" -eq 0 ]; then
        # Try to detect web user
        if id "www-data" &>/dev/null; then
            WEB_USER="www-data"
        elif id "apache" &>/dev/null; then
            WEB_USER="apache"
        elif id "nginx" &>/dev/null; then
            WEB_USER="nginx"
        else
            WEB_USER="www-data"  # Default
        fi
        
        print_status "Setting ownership to $WEB_USER"
        chown -R $WEB_USER:$WEB_USER storage bootstrap/cache
    fi
    
    print_success "Permissions set correctly"
}

# Function to run database migrations
run_migrations() {
    print_header "🗄️ Running Database Migrations"
    
    # Test database connection first
    if php artisan migrate:status >/dev/null 2>&1; then
        print_status "Database connection successful"
        
        # Run migrations
        php artisan migrate --force
        print_success "Database migrations completed"
        
        # Run seeders if they exist
        if php artisan db:seed --help >/dev/null 2>&1; then
            print_status "Running database seeders..."
            php artisan db:seed --force || print_warning "Seeders failed or don't exist"
        fi
    else
        print_warning "Cannot connect to database. Please check your .env configuration"
        print_status "You can run migrations later with: php artisan migrate"
    fi
}

# Function to optimize Laravel
optimize_laravel() {
    print_header "⚡ Optimizing Laravel"
    
    # Clear all caches first
    print_status "Clearing caches..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    
    # Optimize for production
    print_status "Optimizing for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Create symbolic link for storage
    if [ ! -L "public/storage" ]; then
        php artisan storage:link
        print_success "Storage link created"
    fi
    
    print_success "Laravel optimization completed"
}

# Function to create health check endpoint
create_health_check() {
    print_header "🏥 Creating Health Check"
    
    # Create a simple health check route
    if ! grep -q "health" routes/web.php; then
        echo "" >> routes/web.php
        echo "// Health check endpoint" >> routes/web.php
        echo "Route::get('/health', function () {" >> routes/web.php
        echo "    return response()->json([" >> routes/web.php
        echo "        'status' => 'ok'," >> routes/web.php
        echo "        'timestamp' => now()->toISOString()," >> routes/web.php
        echo "        'app' => config('app.name')," >> routes/web.php
        echo "        'version' => '1.0.0'" >> routes/web.php
        echo "    ]);" >> routes/web.php
        echo "});" >> routes/web.php
        
        print_success "Health check endpoint created at /health"
    fi
}

# Function to create deployment info
create_deployment_info() {
    print_header "📋 Creating Deployment Information"
    
    cat > deployment-info.json << EOF
{
    "deployed_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
    "environment": "$ENVIRONMENT",
    "php_version": "$(php -r 'echo PHP_VERSION;')",
    "laravel_version": "$(php artisan --version | grep -oP 'Laravel Framework \K[0-9.]+')",
    "server_info": {
        "hostname": "$(hostname)",
        "os": "$(uname -s)",
        "arch": "$(uname -m)"
    }
}
EOF
    
    print_success "Deployment info saved to deployment-info.json"
}

# Function to test installation
test_installation() {
    print_header "🧪 Testing Installation"
    
    # Test basic Laravel functionality
    if php artisan route:list >/dev/null 2>&1; then
        print_success "✓ Laravel routes are working"
    else
        print_error "✗ Laravel routes test failed"
    fi
    
    # Test database connection
    if php artisan migrate:status >/dev/null 2>&1; then
        print_success "✓ Database connection is working"
    else
        print_warning "⚠ Database connection test failed"
    fi
    
    # Test cache functionality
    if php artisan cache:clear >/dev/null 2>&1; then
        print_success "✓ Cache system is working"
    else
        print_warning "⚠ Cache system test failed"
    fi
    
    # Test health check endpoint
    if command_exists curl && curl -f -s "http://localhost/health" >/dev/null 2>&1; then
        print_success "✓ Health check endpoint is working"
    else
        print_warning "⚠ Health check endpoint test failed (this is normal if web server is not configured)"
    fi
}

# Function to show completion message
show_completion_message() {
    print_header "🎉 Installation Complete!"
    
    echo -e "${GREEN}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "   $PROJECT_NAME has been installed successfully!"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo -e "${NC}"
    
    echo "🔗 Next Steps:"
    echo "   1. Update database credentials in .env file"
    echo "   2. Set Apple Wallet password in .env file"
    echo "   3. Configure your web server to point to public/ directory"
    echo "   4. Run: php artisan migrate (if database setup is complete)"
    echo ""
    echo "📁 Important Files:"
    echo "   • Configuration: .env"
    echo "   • Logs: storage/logs/"
    echo "   • Health Check: /health"
    echo "   • Deployment Info: deployment-info.json"
    echo ""
    echo "🛠️ Useful Commands:"
    echo "   • Check status: php artisan about"
    echo "   • Clear cache: php artisan optimize:clear"
    echo "   • Update app: php artisan optimize"
    echo ""
    
    if [ -f ".env.backup"* ]; then
        echo "📋 Note: Your original .env file has been backed up"
    fi
}

# Main installation process
main() {
    print_header "🚀 Laravel Auto Installation Starting"
    
    # Check if we're in a Laravel project
    if [ ! -f "artisan" ]; then
        print_error "This doesn't appear to be a Laravel project (artisan not found)"
        exit 1
    fi
    
    detect_environment
    check_php_requirements
    check_composer
    install_dependencies
    create_env_file
    generate_app_key
    set_permissions
    run_migrations
    optimize_laravel
    create_health_check
    create_deployment_info
    test_installation
    show_completion_message
    
    print_success "🎉 All done! Your Laravel application is ready to go!"
}

# Handle script interruption
trap 'print_error "Installation interrupted!"; exit 1' INT TERM

# Run main function
main "$@"