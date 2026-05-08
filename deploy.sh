#!/bin/bash

# AWS EC2 Production Deployment Script for KHDownloader

echo "🚀 Starting Production Setup..."

# 1. Update and Install Dependencies
sudo apt update && sudo apt upgrade -y
sudo apt install -y docker.io docker-compose git

# 2. Setup 2GB Swap File for stability
if [ ! -f /swapfile ]; then
    echo "Creating swap file..."
    sudo fallocate -l 2G /swapfile
    sudo chmod 600 /swapfile
    sudo mkswap /swapfile
    sudo swapon /swapfile
    echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
fi

# 3. Fix Docker permissions
sudo usermod -aG docker $USER

# 4. Prepare Directories
mkdir -p storage/app/yt-dlp-temp
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "✅ System ready."
echo "👉 NEXT STEPS:"
echo "1. Update .env.production with your APP_KEY and AWS IP."
echo "2. Run: docker-compose -f docker-compose.prod.yml up -d --build"
echo "3. Run: docker exec kh_app php artisan key:generate"
echo "4. Run: docker exec kh_app php artisan migrate --force"
