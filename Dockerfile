# Use PHP 8.2 with required extensions
# Updated: Fixed libcurl dependency
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libcurl4-openssl-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring xml curl zip gd

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Install composer dependencies (without running scripts)
RUN COMPOSER_ALLOW_SUPERUSER=1 php composer.phar install --no-dev --no-scripts --optimize-autoloader

# Create required directories and set permissions
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs && \
    chmod -R 775 bootstrap/cache storage

# Cache Laravel configuration
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Expose port
EXPOSE 8080

# Start command
CMD php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=$PORT
