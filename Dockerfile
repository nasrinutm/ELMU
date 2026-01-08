FROM richarvey/nginx-php-fpm:latest

# 1. Install Node.js from Alpine Edge (This guarantees v22+)
RUN apk add --no-cache --repository=http://dl-cdn.alpinelinux.org/alpine/edge/main/ nodejs npm

# 2. Environment Setup
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV RUN_SCRIPTS 1
ENV COMPOSER_ALLOW_SUPERUSER 1

# 3. Copy files
COPY . .

# 4. PHP Build
RUN composer install --no-dev --optimize-autoloader

# 5. Build Vue/Vite Assets
# With Node v22, crypto.hash will finally be available
RUN npm install
RUN npm run build

# 6. Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache