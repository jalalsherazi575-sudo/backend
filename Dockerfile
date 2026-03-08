# Use PHP 8.2 with required extensions
# Updated: Fixed composer autoload issues - 2026-03-07
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

# Remove any cached config/service provider files
RUN rm -rf bootstrap/cache/*.php storage/framework/cache/* storage/framework/sessions/* storage/framework/views/*

# Install composer dependencies (including dev for service providers)
RUN COMPOSER_ALLOW_SUPERUSER=1 php composer.phar install --no-scripts

# Create required directories and set permissions
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs && \
    chmod -R 775 bootstrap/cache storage

# Expose port
EXPOSE 8080

# Start command
CMD php artisan config:clear && \
    php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=$PORT
