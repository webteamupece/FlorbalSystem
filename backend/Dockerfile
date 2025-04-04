FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite if needed
RUN a2enmod rewrite

# Copy everything (your frontend and backend are here)
COPY . /var/www/html
