FROM php:8.2-apache

# Install MySQL extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy backend files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Optional: set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
