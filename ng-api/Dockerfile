FROM mcr.microsoft.com/dotnet/sdk:10.0 AS build
WORKDIR /App

COPY . ./
RUN dotnet restore
RUN dotnet publish -o out

FROM mcr.microsoft.com/dotnet/aspnet:10.0

LABEL org.opencontainers.image.vendor="Netfoe" \
    org.opencontainers.image.authors="Santiago Lizardo" \
    org.opencontainers.image.title="Reconmap NextGen API" \
    org.opencontainers.image.description="Rest API for Reconmap" \
    org.opencontainers.image.licenses="Apache-2.0" \
    org.opencontainers.image.url="https://github.com/reconmap/reconmap" \
    org.opencontainers.image.source="https://github.com/reconmap/rest-api" \
    org.opencontainers.image.documentation="https://github.com/reconmap/documentation"

WORKDIR /App
COPY --from=build /App/out .
ENTRYPOINT ["/App/ReconmapNg"]
