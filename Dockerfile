FROM richarvey/nginx-php-fpm:latest

# 1. Install CURRENT Node (This is the critical fix for crypto.hash error)
RUN apk add --no-cache nodejs-current npm

# 2. Environment Setup
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV RUN_SCRIPTS 1
ENV COMPOSER_ALLOW_SUPERUSER 1

# 3. Copy files
COPY . .

# 4. PHP Build
RUN composer install --no-dev --optimize-autoloader

# 5. Frontend Build (Vite 7 requires Node 20+)
RUN npm install
RUN npm run build

# 6. Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache