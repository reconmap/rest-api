FROM debian:buster-slim

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get upgrade -y --fix-missing
RUN apt-get install -y wget unzip lsb-release
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list
RUN apt-get update
RUN apt-get install -y php8.0 php8.0-cli php8.0-fpm php8.0-mysqlnd php8.0-dom php8.0-mbstring php8.0-intl php8.0-xdebug php8.0-curl
RUN apt-get install -y nginx

RUN apt-get update && apt-get install -y php8.0-dev php-pear && \
    pecl channel-update pecl.php.net && \
    pecl install redis && \
    bash -c "echo extension=redis.so | tee /etc/php/8.0/{fpm,cli}/conf.d/30-redis.ini > /dev/null"

RUN wget --no-verbose https://getcomposer.org/installer -O - -q | php -- --install-dir=/usr/local/bin/ --filename=composer --quiet

RUN apt-get install -y fontconfig xfonts-base xfonts-75dpi libxrender1 libxext6
ARG wkhtml_filename=wkhtmltox_0.12.6-1.buster_amd64.deb
RUN wget -q https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/$wkhtml_filename && \
	dpkg -i $wkhtml_filename && \
	rm $wkhtml_filename

RUN apt-get install -y cron
COPY docker/api/crontab.txt /tmp/crontab
RUN crontab /tmp/crontab && rm /tmp/crontab

RUN sed -i "s/;clear_env = no/clear_env = no/" /etc/php/8.0/fpm/pool.d/www.conf
RUN rm /etc/nginx/sites-enabled/default
COPY docker/api/nginx/sites-enabled/* /etc/nginx/sites-enabled/

RUN sed -i 's/upload_max_filesize = [[:digit:]]\+M/upload_max_filesize = 20M/' /etc/php/8.0/fpm/php.ini
RUN sed -i 's/post_max_size = [[:digit:]]\+M/post_max_size = 28M/' /etc/php/8.0/fpm/php.ini

WORKDIR /var/www/webapp
COPY composer.json /var/www/webapp
COPY composer.lock /var/www/webapp
RUN composer install

RUN mkdir -p data/attachments && chown www-data data/attachments
RUN mkdir logs && chown www-data logs && chmod a+w logs

COPY public /var/www/webapp/public
COPY resources /var/www/webapp/resources
COPY src /var/www/webapp/src
VOLUME /var/www/webapp

COPY docker/api/entrypoint.sh /entrypoint
ENTRYPOINT ["/entrypoint"]
CMD nginx -g 'daemon off;' && bash

