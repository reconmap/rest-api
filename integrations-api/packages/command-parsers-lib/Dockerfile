FROM php:8.5-cli

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y git libzip-dev

RUN docker-php-ext-install zip
RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

