FROM ubuntu:20.04

ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update
RUN apt-get install -y vim wget unzip
RUN apt-get install -y php7.4 php7.4-cli php7.4-fpm
RUN apt-get install -y php7.4-mysqlnd php7.4-dom php7.4-mbstring php7.4-intl php7.4-xdebug php7.4-curl
RUN apt-get install -y nginx
RUN apt-get install -y cron

RUN apt-get update && apt-get install -y php7.4-dev php-pear && \
    pecl channel-update pecl.php.net && \
    pecl install redis && \
    bash -c "echo extension=redis.so | tee /etc/php/7.4/{fpm,cli}/conf.d/30-redis.ini > /dev/null"

RUN wget https://getcomposer.org/installer -O - -q | php -- --install-dir=/usr/local/bin/ --filename=composer --quiet

ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.7.3/wait /usr/local/bin/wait
RUN chmod +x /usr/local/bin/wait

RUN apt-get install -y cron
COPY crontab.txt /tmp/crontab
RUN crontab /tmp/crontab

RUN sed -i "s/;clear_env = no/clear_env = no/" /etc/php/7.4/fpm/pool.d/www.conf
RUN rm /etc/nginx/sites-enabled/default
COPY nginx/sites-enabled/* /etc/nginx/sites-enabled/

COPY entrypoint.sh /entrypoint
ENTRYPOINT ["/entrypoint"]
CMD nginx -g 'daemon off;' && bash
