FROM php:8.4-rc-apache-trixie

# Install system dependencies
RUN apt-get update && apt-get install -y \
    composer \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    npm \
    zip \
    unzip

# Enable Apache modules
RUN a2enmod rewrite

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Set working directory
WORKDIR /var/www/html

# Initialize application via composer
RUN cd /var/www/html && \
    php composer.phar install --no-interaction --no-dev --optimize-autoloader

# Change Apache document root to public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Add Apache configuration for Slim
COPY ./docker/000-default.conf /etc/apache2/sites-available/000-default.conf
