# Stage 1: Build Composer Dependencies
FROM php:8.4-cli AS vendor
# Install composer
RUN apt-get update && apt-get upgrade -y && apt-get install -y git unzip && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY composer.json composer.lock ./
# Install dependencies without scripts and autoloader first to leverage Docker cache
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs
COPY . .
# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# Stage 2: Build Frontend Assets (Vite & Tailwind)
FROM node:22-alpine AS frontend
RUN apk update && apk upgrade
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 3: Final Production Image
FROM php:8.4-apache

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Install required system packages and PHP extensions for PostgreSQL
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    libpq-dev \
    unzip \
    && docker-php-ext-install pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Update Apache DocumentRoot to point to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy the rest of the application files
COPY . .

# Copy built vendor directory from the vendor stage
COPY --from=vendor /app/vendor/ ./vendor/

# Copy built frontend assets from the frontend stage
COPY --from=frontend /app/public/build/ ./public/build/

# Ensure proper permissions for Laravel's storage and cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
