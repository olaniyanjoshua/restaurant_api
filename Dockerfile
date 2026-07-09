FROM richarvey/nginx-php-fpm:3.1.6

# 1. Copy the entire project first so 'artisan' and 'composer.json' are present
COPY . .

# 2. Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# 3. Install dependencies
# This creates the /vendor folder
RUN composer install --no-dev --optimize-autoloader

# 4. Clear the cache, routes, and config
# We use DB_CONNECTION=null to prevent Laravel from trying to connect 
# to the database (SQLite) during the build process.
RUN php artisan route:clear && \
    php artisan config:clear && \
    CACHE_STORE=file php artisan cache:clear
# Image config
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1

# Laravel config
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV DB_CONNECTION=pgsql

CMD ["/start.sh"]