FROM ubuntu:20.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get upgrade -y --fix-missing
RUN apt-get install -y vim wget unzip
RUN apt-get install -y php7.4 php7.4-cli php7.4-fpm php7.4-mysqlnd php7.4-dom php7.4-mbstring php7.4-intl php7.4-xdebug php7.4-curl
RUN apt-get install -y nginx
RUN apt-get install -y cron

RUN apt-get update && apt-get install -y php7.4-dev php-pear && \
    pecl channel-update pecl.php.net && \
    pecl install redis && \
    bash -c "echo extension=redis.so | tee /etc/php/7.4/{fpm,cli}/conf.d/30-redis.ini > /dev/null"

RUN wget --no-verbose https://getcomposer.org/installer -O - -q | php -- --install-dir=/usr/local/bin/ --filename=composer --quiet

RUN apt-get install -y fontconfig xfonts-base xfonts-75dpi libxrender1 libxext6
RUN wget -q https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.focal_amd64.deb && \
	dpkg -i wkhtmltox_0.12.6-1.focal_amd64.deb && \
	rm wkhtmltox_0.12.6-1.focal_amd64.deb

ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.7.3/wait /usr/local/bin/wait
RUN chmod +x /usr/local/bin/wait

RUN apt-get install -y cron
COPY docker/api/crontab.txt /tmp/crontab
RUN crontab /tmp/crontab

RUN sed -i "s/;clear_env = no/clear_env = no/" /etc/php/7.4/fpm/pool.d/www.conf
RUN rm /etc/nginx/sites-enabled/default
COPY docker/api/nginx/sites-enabled/* /etc/nginx/sites-enabled/

WORKDIR /var/www/webapp
COPY composer.json /var/www/webapp
COPY composer.lock /var/www/webapp
RUN composer install

RUN mkdir -p data/attachments && chown www-data data/attachments
RUN mkdir -p data/reports && chown www-data data/reports
RUN mkdir logs && chown www-data logs && chmod a+w logs

COPY public /var/www/webapp/public
COPY resources /var/www/webapp/resources
COPY src /var/www/webapp/src
VOLUME /var/www/webapp

COPY docker/api/entrypoint.sh /entrypoint
ENTRYPOINT ["/entrypoint"]
CMD nginx -g 'daemon off;' && bash

