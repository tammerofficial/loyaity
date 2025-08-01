#!/bin/bash

# 🔄 Auto Deploy Script for Laravel
# سكريبت النشر التلقائي للارافيل
# Handles git commits, backups, and deployment automatically

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

# Configuration
PROJECT_NAME="Tammer Loyalty System"
BACKUP_DIR="backups"
DB_BACKUP_DIR="$BACKUP_DIR/database"
FILES_BACKUP_DIR="$BACKUP_DIR/files"

print_header() {
    echo -e "${BLUE}"
    echo "=================================================================="
    echo "  🚀 $1"
    echo "=================================================================="
    echo -e "${NC}"
}

print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Function to check if we're in a git repository
check_git_repo() {
    if [ ! -d ".git" ]; then
        print_error "This is not a git repository!"
        print_status "Initializing git repository..."
        git init
        git add .
        git commit -m "Initial commit"
        print_success "Git repository initialized"
    fi
}

# Function to get the next version number
get_next_version() {
    # Get the last commit message
    LAST_COMMIT=$(git log -1 --pretty=%B 2>/dev/null || echo "")
    
    if [[ $LAST_COMMIT =~ ^v([0-9]+)\.([0-9]+)$ ]]; then
        MAJOR=${BASH_REMATCH[1]}
        MINOR=${BASH_REMATCH[2]}
        
        if [ "$MINOR" -lt 9 ]; then
            NEXT_VERSION="v$MAJOR.$((MINOR + 1))"
        else
            NEXT_VERSION="v$((MAJOR + 1)).0"
        fi
    else
        NEXT_VERSION="v1.0"
    fi
    
    echo $NEXT_VERSION
}

# Function to create database backup
create_database_backup() {
    print_header "💾 Creating Database Backup"
    
    # Create backup directory
    mkdir -p "$DB_BACKUP_DIR"
    
    # Get database configuration from .env
    if [ -f ".env" ]; then
        DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)
        DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
        DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)
        DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2 | sed 's/127.0.0.1/localhost/')
        
        if [ ! -z "$DB_DATABASE" ] && [ ! -z "$DB_USERNAME" ]; then
            BACKUP_FILE="$DB_BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql"
            
            print_status "Backing up database: $DB_DATABASE"
            
            # Create mysqldump command
            if [ ! -z "$DB_PASSWORD" ]; then
                mysqldump -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_FILE" 2>/dev/null
            else
                mysqldump -h"$DB_HOST" -u"$DB_USERNAME" "$DB_DATABASE" > "$BACKUP_FILE" 2>/dev/null
            fi
            
            if [ $? -eq 0 ]; then
                print_success "Database backup created: $BACKUP_FILE"
                
                # Compress the backup
                gzip "$BACKUP_FILE"
                print_success "Backup compressed: ${BACKUP_FILE}.gz"
                
                # Keep only last 5 backups
                cd "$DB_BACKUP_DIR"
                ls -t backup_*.sql.gz | tail -n +6 | xargs -r rm
                cd - > /dev/null
                
                return 0
            else
                print_warning "Database backup failed - continuing without backup"
                return 1
            fi
        else
            print_warning "Database configuration not found in .env - skipping backup"
            return 1
        fi
    else
        print_warning ".env file not found - skipping database backup"
        return 1
    fi
}

# Function to create files backup
create_files_backup() {
    print_header "📁 Creating Files Backup"
    
    mkdir -p "$FILES_BACKUP_DIR"
    
    BACKUP_FILE="$FILES_BACKUP_DIR/files_backup_$(date +%Y%m%d_%H%M%S).tar.gz"
    
    print_status "Creating files backup..."
    
    # Create backup excluding unnecessary files
    tar -czf "$BACKUP_FILE" \
        --exclude='.git' \
        --exclude='node_modules' \
        --exclude='vendor' \
        --exclude='storage/logs/*' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        --exclude="$BACKUP_DIR" \
        . 2>/dev/null
    
    if [ $? -eq 0 ]; then
        print_success "Files backup created: $BACKUP_FILE"
        
        # Keep only last 3 file backups
        cd "$FILES_BACKUP_DIR"
        ls -t files_backup_*.tar.gz | tail -n +4 | xargs -r rm
        cd - > /dev/null
        
        return 0
    else
        print_error "Files backup failed"
        return 1
    fi
}

# Function to check for uncommitted changes
check_uncommitted_changes() {
    if [ -n "$(git status --porcelain)" ]; then
        return 0  # Has changes
    else
        return 1  # No changes
    fi
}

# Function to commit and push changes
commit_and_push() {
    print_header "📝 Committing Changes"
    
    if ! check_uncommitted_changes; then
        print_warning "No uncommitted changes found"
        return 1
    fi
    
    # Get next version
    NEXT_VERSION=$(get_next_version)
    print_status "Next version: $NEXT_VERSION"
    
    # Add all changes
    print_status "Adding changes to git..."
    git add .
    
    # Commit with version
    print_status "Committing changes..."
    git commit -m "$NEXT_VERSION"
    
    # Push to current branch
    CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
    print_status "Pushing to origin/$CURRENT_BRANCH..."
    
    git push origin "$CURRENT_BRANCH"
    
    if [ $? -eq 0 ]; then
        print_success "Changes committed and pushed as $NEXT_VERSION"
        return 0
    else
        print_error "Failed to push changes"
        return 1
    fi
}

# Function to optimize Laravel after deployment
optimize_laravel() {
    print_header "⚡ Optimizing Laravel"
    
    # Clear all caches
    print_status "Clearing caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    # Run migrations
    print_status "Running migrations..."
    php artisan migrate --force
    
    # Optimize for production
    print_status "Caching configuration..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Create storage link if not exists
    if [ ! -L "public/storage" ]; then
        php artisan storage:link
        print_success "Storage link created"
    fi
    
    # Set permissions
    print_status "Setting permissions..."
    chmod -R 755 storage
    chmod -R 755 bootstrap/cache
    
    print_success "Laravel optimization completed"
}

# Function to restore database from backup
restore_database() {
    print_header "🔄 Database Restoration"
    
    if [ ! -d "$DB_BACKUP_DIR" ]; then
        print_error "No backup directory found"
        return 1
    fi
    
    LATEST_BACKUP=$(ls -t "$DB_BACKUP_DIR"/backup_*.sql.gz 2>/dev/null | head -1)
    
    if [ -z "$LATEST_BACKUP" ]; then
        print_error "No database backups found"
        return 1
    fi
    
    echo -n "Do you want to restore the database from the latest backup? [y/N]: "
    read -r CONFIRM
    
    if [[ $CONFIRM =~ ^[Yy]$ ]]; then
        print_status "Restoring database from: $LATEST_BACKUP"
        
        # Get database config
        DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)
        DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
        DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)
        DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2 | sed 's/127.0.0.1/localhost/')
        
        # Decompress and restore
        gunzip -c "$LATEST_BACKUP" | mysql -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
        
        if [ $? -eq 0 ]; then
            print_success "Database restored successfully"
        else
            print_error "Database restoration failed"
            return 1
        fi
    else
        print_status "Database restoration cancelled"
    fi
}

# Function to show deployment status
show_deployment_status() {
    print_header "📊 Deployment Status"
    
    # Git status
    echo -e "${PURPLE}Git Information:${NC}"
    echo "  Branch: $(git rev-parse --abbrev-ref HEAD)"
    echo "  Last Commit: $(git log -1 --pretty=%B | tr -d '\n')"
    echo "  Commit Hash: $(git rev-parse --short HEAD)"
    echo "  Remote URL: $(git remote get-url origin 2>/dev/null || echo 'No remote set')"
    echo
    
    # Laravel status
    echo -e "${PURPLE}Laravel Information:${NC}"
    echo "  Environment: $(grep "^APP_ENV=" .env | cut -d '=' -f2 2>/dev/null || echo 'Unknown')"
    echo "  Debug Mode: $(grep "^APP_DEBUG=" .env | cut -d '=' -f2 2>/dev/null || echo 'Unknown')"
    echo "  URL: $(grep "^APP_URL=" .env | cut -d '=' -f2 2>/dev/null || echo 'Unknown')"
    echo
    
    # Backup information
    echo -e "${PURPLE}Backup Information:${NC}"
    if [ -d "$BACKUP_DIR" ]; then
        echo "  Database Backups: $(ls -1 "$DB_BACKUP_DIR"/*.gz 2>/dev/null | wc -l)"
        echo "  Files Backups: $(ls -1 "$FILES_BACKUP_DIR"/*.tar.gz 2>/dev/null | wc -l)"
        
        LATEST_DB_BACKUP=$(ls -t "$DB_BACKUP_DIR"/*.gz 2>/dev/null | head -1)
        if [ ! -z "$LATEST_DB_BACKUP" ]; then
            echo "  Latest DB Backup: $(basename "$LATEST_DB_BACKUP") ($(date -r "$LATEST_DB_BACKUP" '+%Y-%m-%d %H:%M:%S'))"
        fi
    else
        echo "  No backups found"
    fi
}

# Function to check for deletion operations
check_deletion_safety() {
    print_warning "Checking for potentially destructive operations..."
    
    # This is a safety check as per user's rules
    echo -n "Are you sure you want to proceed with deployment? Type YES to confirm: "
    read -r CONFIRM
    
    if [ "$CONFIRM" != "YES" ]; then
        print_error "Deployment cancelled for safety"
        exit 1
    fi
}

# Main deployment function
deploy() {
    print_header "Auto Deployment Starting"
    
    # Safety check
    check_deletion_safety
    
    # Check if we're in a Laravel project
    if [ ! -f "artisan" ]; then
        print_error "This is not a Laravel project (artisan not found)"
        exit 1
    fi
    
    # Check git repository
    check_git_repo
    
    # Create backups before deployment
    create_database_backup
    create_files_backup
    
    # Commit and push changes if any
    if check_uncommitted_changes; then
        commit_and_push
    else
        print_warning "No changes to commit"
    fi
    
    # Optimize Laravel
    optimize_laravel
    
    # Show deployment status
    show_deployment_status
    
    print_success "🎉 Deployment completed successfully!"
    
    # Create deployment log
    echo "$(date): Deployment completed successfully" >> "storage/logs/deployment.log"
}

# Handle script arguments
case "${1:-deploy}" in
    "deploy"|"")
        deploy
        ;;
    "backup")
        print_header "Manual Backup"
        create_database_backup
        create_files_backup
        print_success "Backup completed"
        ;;
    "restore")
        restore_database
        ;;
    "status")
        show_deployment_status
        ;;
    "optimize")
        optimize_laravel
        ;;
    "help"|"-h"|"--help")
        echo "Usage: $0 [command]"
        echo
        echo "Commands:"
        echo "  deploy    - Full deployment (default)"
        echo "  backup    - Create manual backup"
        echo "  restore   - Restore from latest backup"
        echo "  status    - Show deployment status"
        echo "  optimize  - Optimize Laravel only"
        echo "  help      - Show this help"
        ;;
    *)
        print_error "Unknown command: $1"
        echo "Use '$0 help' for available commands"
        exit 1
        ;;
esac