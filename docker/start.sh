#!/bin/bash

# Redis bağlantısını kontrol et
echo "Redis bağlantısı kontrol ediliyor..."
until /usr/local/bin/test-redis.sh
do
    echo "Redis bağlantısı bekleniyor..."
    sleep 1
done

# Laravel storage izinlerini ayarla
chmod -R 775 /var/www/storage /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Migrations çalıştır
php artisan migrate --force

# Cache temizle ve yapılandır
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Redis cache'i temizle
php artisan cache:clear

# Supervisor ile servisleri başlat
/usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf 