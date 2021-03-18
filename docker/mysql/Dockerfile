FROM mysql:8.0

LABEL maintainer="Reconmap engineering" \
    org.opencontainers.image.title="Reconmap Rest API database" \
    org.opencontainers.image.authors="Santiago Lizardo" \
    org.opencontainers.image.vendor="Reconmap" \
    org.opencontainers.image.documentation="https://reconmap.org" \
    org.opencontainers.image.licenses="GPL" \
    org.opencontainers.image.url="https://github.com/reconmap/rest-api"

COPY database/01-schema.sql /docker-entrypoint-initdb.d/
COPY database/02-default-data.sql /docker-entrypoint-initdb.d/
