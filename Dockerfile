# Build frontend assets
FROM node:20 AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Setup PHP-FPM
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    ca-certificates

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
# pdo_sqlite is needed for Laravel SQLite databases.
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

# Copy custom php.ini
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY . .

# Copy built frontend assets from node stage
COPY --from=node_builder /app/public/build /var/www/public/build

# Install PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --optimize-autoloader --no-dev

# Ensure permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database

# Create SQLite database if it doesn't exist and run migrations
RUN touch /var/www/database/database.sqlite && \
    chown www-data:www-data /var/www/database/database.sqlite && \
    php artisan migrate --force

EXPOSE 9000
CMD ["php-fpm"]
