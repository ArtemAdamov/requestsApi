# Use the PHP 8.1 image with FPM support
FROM php:8.2-fpm

RUN apt-get update -y && apt-get install -y curl zip unzip libmcrypt-dev

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update

# Install Postgre PDO
RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Install dependencies
RUN composer install
## Use the Ubuntu 18.04 image
#FROM ubuntu:18.04
#
## Update and upgrade Ubuntu packages
#RUN apt-get update && apt-get upgrade -y
#
## Install necessary packages
#RUN apt-get install -y curl zip unzip git
#
## Install PHP and necessary PHP extensions
#RUN apt-get install -y php php-curl php-mbstring php-pgsql php-xml php-gd
#
## Install Composer
#RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#
## Set working directory
#WORKDIR /var/www/symfony
#
## Copy application files
#COPY . /var/www/symfony
#
## Install dependencies
#RUN composer install
