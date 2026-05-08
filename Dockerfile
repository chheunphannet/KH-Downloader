FROM node:24-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm ci && npm run build

FROM php:8.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        ffmpeg \
        git \
        libcurl4-openssl-dev \
        libonig-dev \
        libxml2-dev \
        python3 \
        unzip \
    && docker-php-ext-install curl dom mbstring pdo_mysql \
    && curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/khdownloader.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --optimize-autoloader

COPY . .
COPY --from=assets /app/public/build ./public/build
COPY docker/entrypoint.sh /usr/local/bin/khdownloader-entrypoint

RUN composer dump-autoload --optimize \
    && mkdir -p storage/app/yt-dlp-temp storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views bootstrap/cache \
    && chmod +x /usr/local/bin/khdownloader-entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["khdownloader-entrypoint"]
CMD ["php-fpm"]
