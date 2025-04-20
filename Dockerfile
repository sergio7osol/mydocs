FROM php:8.1-fpm

# Dockerfile for mydocs Project
LABEL project="mydocs"

# Install system dependencies
RUN apt-get update && apt-get install -y git vim libzip-dev

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo_mysql zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html
