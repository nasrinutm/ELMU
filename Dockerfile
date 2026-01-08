# --- STAGE 1: Build Assets (Matching your Node v22.12) ---
FROM node:22.12-alpine AS asset-builder
WORKDIR /app

# Install dependencies first to leverage Docker cache
COPY package*.json ./
RUN npm install

# Copy all files and build Vite assets
COPY . .
RUN npm run build

# --- STAGE 2: Run Application (Matching your PHP 8.5) ---
FROM php:8.5-fpm-alpine
WORKDIR /var/www/html

# 1. Install System Dependencies (Added postgresql-dev for Supabase compatibility)
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    icu-dev \
    postgresql-dev

# 2. Install PHP Extensions required by Laravel
RUN docker-php-ext-install pdo_mysql pdo_pgsql bcmath gd zip intl

# 3. Copy Application Files
COPY . .

# 4. Copy ONLY the compiled assets from Stage 1
# This fixes the "Vite Manifest Not Found" error
COPY --from=asset-builder /app/public/build ./public/build

# 5. Install Composer Dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# 6. Set Permissions for Laravel
# Ensures the web server can write to the cache and storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Configure Nginx (Assumes you renamed the file to conf/nginx.conf)
COPY conf/nginx.conf /etc/nginx/http.d/default.conf

# 8. Set Production Environment
ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 80

# Start PHP-FPM in background and Nginx in foreground
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]