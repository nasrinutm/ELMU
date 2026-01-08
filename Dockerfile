FROM richarvey/nginx-php-fpm:latest

# 1. Force the upgrade of Node.js to version 20 or 22
# We use --force-overwrite because the base image has a "locked" node 18 version
RUN apk update && \
    apk upgrade && \
    apk add --no-cache --force-overwrite nodejs npm

# 2. Environment Setup
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV RUN_SCRIPTS 1
ENV COMPOSER_ALLOW_SUPERUSER 1

# 3. Copy files
COPY . .

# 4. PHP Build (Laravel dependencies)
RUN composer install --no-dev --optimize-autoloader

# 5. Frontend Build
# This step will now see Node 20+ and crypto.hash will work
RUN npm install
RUN npm run build

# 6. Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache