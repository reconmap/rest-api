FROM mysql:8.0

LABEL maintainer="https://reconmap.org"

COPY docker/mysql/initdb.d/01-schema.sql /docker-entrypoint-initdb.d/
COPY docker/mysql/initdb.d/02-default-data.sql /docker-entrypoint-initdb.d/

