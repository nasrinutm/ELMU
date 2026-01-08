# Using a pre-built image that includes Nginx and PHP-FPM
FROM richarvey/nginx-php-fpm:latest

# 1. INSTALL LATEST NODE & NPM (Updated to nodejs-current for Prisma 7 support)
RUN apk add --no-cache nodejs-current npm

# 2. Set Environment Variables
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV RUN_SCRIPTS 1
ENV COMPOSER_ALLOW_SUPERUSER 1

# 3. Copy project files
COPY . .

# 4. Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# 5. Build Vue/Vite Assets
# We run 'npm install' then 'npm run build' to generate the manifest.json
RUN npm install
RUN npm run build

# 6. FIX PERMISSIONS
RUN mkdir -p /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/framework/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache