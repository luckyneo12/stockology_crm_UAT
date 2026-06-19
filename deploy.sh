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

# 5. Restart PM2 background Node services
echo "🔄 Reloading PM2 background services..."
if pm2 describe whatsapp-crm-service > /dev/null 2>&1; then
    pm2 startOrReload ecosystem.config.js --env production
else
    pm2 start ecosystem.config.js --env production
fi

# 6. Clear Laravel Cache
echo "🧹 Clearing Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎉 Deployment completed successfully!"
