FROM richarvey/nginx-php-fpm:latest

# 1. Upgrade system and install Node.js 20+ 
# We use the community repo to get a stable, modern Node version
RUN apk update && \
    apk upgrade && \
    apk add --no-cache nodejs npm

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
# The 'apk upgrade' ensures the system has the libraries Node needs to run
RUN npm install
RUN npm run build

# 6. Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache