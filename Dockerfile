# Start from the official PHP 8.2 image.
# We don't need -fpm since we're not using Nginx to manage it.
# The 'alpine' variant is smaller and recommended.
FROM php:8.2-alpine

# Set the working directory
WORKDIR /var/www/html

# Install the PHP extension for MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Note: The command to start the server will be in the docker-compose.yml