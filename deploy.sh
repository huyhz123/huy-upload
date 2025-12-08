#!/bin/bash

###############################################################################
# Laravel Repair Service & File/Course System - Auto Deploy Script
# Version: 1.0
# Deploy chỉ bằng 1 lệnh: ./deploy.sh
###############################################################################

set -e  # Exit on error

echo "======================================================================"
echo "   Laravel Repair Service - Auto Deploy Script"
echo "======================================================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Check if running as root (optional, comment out if not needed)
# if [ "$EUID" -ne 0 ]; then
#     print_error "Please run as root or with sudo"
#     exit 1
# fi

print_info "Step 1: Checking system requirements..."
sleep 1

# Check PHP
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed. Please install PHP 8.2 or higher."
    exit 1
fi
print_success "PHP found: $(php -v | head -n 1)"

# Check Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install Composer."
    exit 1
fi
print_success "Composer found: $(composer -V)"

# Check Node & NPM
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed. Please install Node.js."
    exit 1
fi
print_success "Node.js found: $(node -v)"

if ! command -v npm &> /dev/null; then
    print_error "NPM is not installed. Please install NPM."
    exit 1
fi
print_success "NPM found: $(npm -v)"

echo ""
print_info "Step 2: Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader
print_success "Composer dependencies installed!"

echo ""
print_info "Step 3: Installing NPM dependencies..."
npm install
print_success "NPM dependencies installed!"

echo ""
print_info "Step 4: Building frontend assets..."
npm run build
print_success "Frontend assets built!"

echo ""
print_info "Step 5: Setting up environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    print_success ".env file created from .env.example"
else
    print_info ".env file already exists, skipping..."
fi

echo ""
print_info "Step 6: Generating application key..."
php artisan key:generate --force
print_success "Application key generated!"

echo ""
print_info "Step 7: Setting up storage permissions..."
chmod -R 775 storage bootstrap/cache
print_success "Storage permissions set!"

echo ""
print_info "Step 8: Creating storage symbolic link..."
php artisan storage:link || print_info "Storage link may already exist"
print_success "Storage link created!"

echo ""
print_info "Step 9: Database setup..."
read -p "Do you want to run migrations now? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_info "Running migrations..."
    php artisan migrate:fresh --force
    print_success "Migrations completed!"
    
    read -p "Do you want to seed demo data? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_info "Seeding database with demo data..."
        php artisan db:seed --force
        print_success "Database seeded!"
        echo ""
        print_info "Demo credentials:"
        echo "  Admin: admin@repair.com / admin123"
        echo "  Staff: staff@repair.com / staff123"
        echo "  Customer: customer1@example.com / password"
    fi
else
    print_info "Skipping database setup. Run manually: php artisan migrate && php artisan db:seed"
fi

echo ""
print_info "Step 10: Clearing and caching configuration..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Cache cleared and rebuilt!"

echo ""
print_info "Step 11: Publishing vendor assets..."
php artisan vendor:publish --all --force || true
print_success "Vendor assets published!"

echo ""
echo "======================================================================"
print_success "Deployment completed successfully!"
echo "======================================================================"
echo ""
print_info "Next steps:"
echo "  1. Update .env file with your database and API credentials"
echo "  2. Configure payment gateways (VNPay, Momo, PayPal, etc.)"
echo "  3. Setup OpenAI API key for chatbot"
echo "  4. Configure mail settings"
echo "  5. Start server: php artisan serve"
echo ""
print_info "Access your application:"
echo "  Frontend: http://localhost:8000"
echo "  Admin: http://localhost:8000/admin/dashboard"
echo ""
echo "======================================================================"
