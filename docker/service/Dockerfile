FROM ubuntu:20.04

ENV DEBIAN_FRONTEND noninteractive 
RUN apt-get update
RUN apt-get install -y vim wget unzip
RUN apt-get install -y php7.4 php7.4-cli php7.4-fpm
RUN apt-get install -y php7.4-mysql php7.4-dom php7.4-mbstring php7.4-intl php7.4-xdebug
RUN apt-get install -y nginx

RUN wget https://getcomposer.org/installer -O - -q | php -- --install-dir=/usr/local/bin/ --filename=composer --quiet

ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.7.3/wait /usr/local/bin/wait
RUN chmod +x /usr/local/bin/wait

RUN rm /etc/nginx/sites-enabled/default
COPY nginx/sites-enabled/* /etc/nginx/sites-enabled/

CMD service php7.4-fpm start && nginx -g 'daemon off;' && bash
