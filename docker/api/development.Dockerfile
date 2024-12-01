FROM quay.io/reconmap/rest-api:latest

RUN groupadd -g ${HOST_GID} reconmappers && \
    useradd -u ${HOST_UID} -g ${HOST_GID} -m -s /bin/bash reconmapper

RUN apt-get update && apt-get upgrade -y --fix-missing
RUN apt-get install -y wget unzip lsb-release
RUN apt-get update
RUN apt-get install -y php${PHP_VERSION}-xdebug

