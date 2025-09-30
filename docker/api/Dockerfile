FROM debian:bookworm-slim

ENV TZ="Europe/London"

LABEL org.opencontainers.image.vendor="Netfoe" \
    org.opencontainers.image.authors="Santiago Lizardo" \
	org.opencontainers.image.title="Reconmap API" \
	org.opencontainers.image.description="Rest API for Reconmap" \
	org.opencontainers.image.licenses="Apache-2.0" \
    org.opencontainers.image.url="https://github.com/reconmap/reconmap" \
	org.opencontainers.image.source="https://github.com/reconmap/rest-api" \
	org.opencontainers.image.documentation="https://github.com/reconmap/documentation"

ARG PHP_VERSION=8.4
ARG DEBIAN_FRONTEND=noninteractive

ARG HOST_UID
ARG HOST_GID

RUN groupadd -g ${HOST_GID} reconmappers && \
    useradd -u ${HOST_UID} -g ${HOST_GID} -m -s /bin/bash reconmapper

RUN apt-get update && apt-get upgrade -y --fix-missing
RUN apt-get install -y wget unzip lsb-release
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list
RUN apt-get update
RUN apt-get install -y php${PHP_VERSION} php${PHP_VERSION}-cli php${PHP_VERSION}-fpm php${PHP_VERSION}-mysqlnd php${PHP_VERSION}-dom php${PHP_VERSION}-mbstring php${PHP_VERSION}-intl php${PHP_VERSION}-curl php${PHP_VERSION}-gd php${PHP_VERSION}-zip
RUN apt-get install -y php${PHP_VERSION}-xdebug
RUN apt-get install -y nginx

RUN apt-get update && apt-get install -y php${PHP_VERSION}-dev php-pear && \
    pecl channel-update pecl.php.net && \
    pecl install redis && \
    bash -c "echo extension=redis.so | tee /etc/php/${PHP_VERSION}/{fpm,cli}/conf.d/30-redis.ini > /dev/null"

RUN wget --no-verbose https://getcomposer.org/installer -O - -q | php -- --install-dir=/usr/local/bin/ --filename=composer --quiet

RUN apt-get install -y cron
COPY docker/api/crontab.txt /tmp/crontab
RUN crontab -u reconmapper /tmp/crontab && rm /tmp/crontab

RUN sed -i "s/;clear_env = no/clear_env = no/" /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
RUN rm /etc/nginx/sites-enabled/default
COPY docker/api/nginx/sites-enabled/* /etc/nginx/sites-enabled/

RUN sed -i 's/upload_max_filesize = [[:digit:]]\+M/upload_max_filesize = 20M/' /etc/php/${PHP_VERSION}/fpm/php.ini
RUN sed -i 's/post_max_size = [[:digit:]]\+M/post_max_size = 28M/' /etc/php/${PHP_VERSION}/fpm/php.ini

RUN mkdir -p /var/www/webapp /var/www/webapp/data/vendor /var/www/webapp/data/attachments /var/www/webapp/data/cache /var/www/webapp/logs && \
    chown -R reconmapper:reconmappers /var/www/webapp

RUN chmod -R a+w /var/www/webapp/logs

WORKDIR /var/www/webapp

COPY composer.json composer.lock /var/www/webapp/
COPY packages/ /var/www/webapp/packages/
COPY src/ /var/www/webapp/src/

RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader

COPY public /var/www/webapp/public
COPY database/ /var/www/webapp/database/
COPY resources/ /var/www/webapp/resources/

VOLUME /var/www/webapp

COPY docker/api/entrypoint.sh /entrypoint

ENTRYPOINT ["/entrypoint"]
CMD nginx -g 'daemon off;' && bash
