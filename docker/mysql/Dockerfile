FROM mysql:8.4

LABEL maintainer="Reconmap engineering" \
    org.opencontainers.image.title="Reconmap Rest API database" \
    org.opencontainers.image.authors="Santiago Lizardo" \
    org.opencontainers.image.vendor="Reconmap" \
    org.opencontainers.image.documentation="https://docs.reconmap.com" \
    org.opencontainers.image.licenses="GPL" \
    org.opencontainers.image.url="https://github.com/reconmap/rest-api"

ARG HOST_UID
ARG HOST_GID

RUN groupadd -g ${HOST_GID} reconmappers && \
    useradd -r -u ${HOST_UID} -g reconmappers reconmapper

COPY database/ /docker-entrypoint-initdb.d/
COPY docker/mysql/overrides.cnf /etc/mysql/conf.d/overrides.cnf

USER reconmapper

