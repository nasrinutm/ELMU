# Using a pre-built image that includes Nginx and PHP-FPM
FROM richarvey/nginx-php-fpm:latest

COPY conf/nginx/nginx-site.conf /etc/nginx/sites-available/default.conf
# Set the web root to Laravel's public folder
ENV WEBROOT /var/www/html/public
ENV APP_ENV production

# Allow composer to run as root (needed for some Docker environments)
ENV COMPOSER_ALLOW_SUPERUSER 1

# Copy your project files into the container
COPY . .

# 1. FIX PERMISSIONS (Crucial for Laravel)
# Sets ownership to the web server user so it can write logs and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 2. Install dependencies
RUN composer install --no-dev --optimize-autoloader

# 3. Tell the image to run your custom deploy script on startup
ENV RUN_SCRIPTS 1