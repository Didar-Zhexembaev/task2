FROM php:7.4-apache
RUN a2enmod rewrite \
&& apt-get update && apt-get upgrade -y \
&& apt-get install libxml2-dev git zip unzip -y \
&& docker-php-ext-install soap
# Install Composer
RUN curl -sS https://getcomposer.org/installer | \
php -- --install-dir=/usr/local/bin --filename=composer