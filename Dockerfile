FROM php:8.2-fpm

# Çevre değişkenlerini tanımla
ARG ALPHA_VANTAGE_API_KEY
ARG ALPHAVANTAGE_API_KEY
ARG GOLDAPI_KEY
ARG GOLDAPI_API_KEY

ENV ALPHA_VANTAGE_API_KEY=${ALPHA_VANTAGE_API_KEY}
ENV ALPHAVANTAGE_API_KEY=${ALPHAVANTAGE_API_KEY}
ENV GOLDAPI_KEY=${GOLDAPI_KEY}
ENV GOLDAPI_API_KEY=${GOLDAPI_API_KEY}

# Sistem paketlerini yükle
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    nginx \
    supervisor \
    redis-tools \
    libicu-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP eklentilerini yükle
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    xml \
    curl \
    zip

# Redis eklentisini yükle
RUN pecl install redis && docker-php-ext-enable redis

# PHP bellek limitini artır
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini

# PHP upload limitlerini artır
RUN echo "upload_max_filesize=50M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/uploads.ini

# Composer'ı yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Nginx konfigürasyonunu kopyala
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Supervisor konfigürasyonunu kopyala
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Uygulama dizinini oluştur
WORKDIR /var/www

# Uygulama dosyalarını kopyala
COPY . /var/www

# Composer bağımlılıklarını yükle
RUN composer install --no-interaction --optimize-autoloader

# Storage dizini izinlerini ayarla
RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Redis bağlantısını test et
COPY docker/test-redis.sh /usr/local/bin/test-redis.sh
RUN chmod +x /usr/local/bin/test-redis.sh

# Başlangıç scriptini kopyala
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Portları aç
EXPOSE 80

# Servisleri başlat
CMD ["/usr/local/bin/start.sh"] 