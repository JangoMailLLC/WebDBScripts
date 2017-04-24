FROM php:5-apache
# FROM php:7.0-apache

# Add mysql support - doesn't with with 7.0+
RUN docker-php-ext-install mysql

# Add mysqli support
RUN docker-php-ext-install mysqli

# Add pdo mysql support
RUN docker-php-ext-install pdo pdo_mysql