#!/bin/bash
# Hostinger Auto-Deployment Automation Script
# Usage: sh deploy.sh

echo "🚀 Starting deployment..."

# 1. Pull latest code from Repository
echo "📥 Pulling latest code..."
git pull origin main

# 2. Install PHP Dependencies & Optimize
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# 3. Run Database Migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 4. Compile React Assets & Install Node dependencies
echo "⚡ Building React Vite assets and installing Node services..."
npm run prod:build

# 5. Restart background Node services
echo "🔄 Reloading background Node services..."
if command -v pm2 &> /dev/null; then
    echo "PM2 detected, restarting via PM2..."
    if pm2 describe whatsapp-crm-service > /dev/null 2>&1; then
        pm2 startOrReload ecosystem.config.js --env production
    else
        pm2 start ecosystem.config.js --env production
    fi
else
    echo "PM2 not found, triggering Passenger restart (Hostinger Cloud)..."
    mkdir -p whatsapp-node-service/tmp
    touch whatsapp-node-service/tmp/restart.txt
    mkdir -p sheet-node-service/tmp
    touch sheet-node-service/tmp/restart.txt
fi

# 6. Clear Laravel Cache
echo "🧹 Clearing Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎉 Deployment completed successfully!"
