FROM richarvey/nginx-php-fpm:latest

# 1. Install standard Node & NPM
RUN apk add --no-cache nodejs npm

# 2. Set Environment Variables
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV RUN_SCRIPTS 1

# 3. Copy project files
COPY . .

# 4. Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# 5. Build Vue/Vite Assets (This will now work without Prisma errors)
RUN npm install
RUN npm run build

# 6. Set Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache